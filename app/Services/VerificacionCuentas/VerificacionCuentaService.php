<?php

declare(strict_types=1);

namespace App\Services\VerificacionCuentas;

use App\Enums\EstadoNotificacionPush;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\CuentaBancaria;
use App\Models\CuentaPorCobrar;
use App\Models\NotificacionPush;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VerificacionCuentaService
{
    /**
     * BL: ID del banco temporal/placeholder que se elimina al validar otra cuenta.
     * En legacy era bank_id = 23.
     */
    private const BANCO_TEMPORAL_ID = 23;

    /**
     * BL: Hora en la que no se envían cuentas a verificación (ventana operativa STP).
     */
    private const HORA_BLOQUEO_ENVIO = 18;

    /**
     * BL: Límite de cuentas por lote de envío.
     */
    private const LIMITE_CUENTAS_POR_LOTE = 100;

    /**
     * BL: Antigüedad mínima del colaborador para enviar cuenta a verificación (meses).
     */
    private const ANTIGUEDAD_MINIMA_MESES = 3;

    /**
     * BL: ID del motivo de notificación para validación exitosa.
     * Referencia: tabla notificaciones_incluidas, seeder ID 2.
     */
    private const MOTIVO_NOTIFICACION_VALIDACION = 2;

    /**
     * BL: ID del motivo de notificación para rechazo.
     * Referencia: tabla notificaciones_incluidas, seeder ID 3.
     */
    private const MOTIVO_NOTIFICACION_RECHAZO = 3;

    /**
     * BL: Códigos de razón de STP que permiten reasignación de adeudos.
     * Corresponden a códigos STP 01 y 03 (almacenados como integer en intentos_cobro.codigo_razon).
     */
    private const CODIGOS_RAZON_REASIGNACION = [1, 3];

    /**
     * BL: Valida una cuenta bancaria.
     * - Cambia estado a VERIFICADA
     * - Marca como cuenta de nómina (única por colaborador)
     * - Elimina cuenta temporal si existe
     */
    public function validarCuenta(CuentaBancaria $cuenta): void
    {
        if (! $cuenta->puedeVerificarse()) {
            throw ValidationException::withMessages([
                'cuenta' => "La cuenta #{$cuenta->numero} no puede verificarse. Estado actual: {$cuenta->estado->getLabel()}",
            ]);
        }

        DB::transaction(function () use ($cuenta): void {
            $cuenta->marcarComoVerificada();

            $this->eliminarCuentaTemporalDelColaborador($cuenta->colaborador_id);
        });

        $this->notificarResultadoVerificacion($cuenta, 'validacion');
    }

    /**
     * BL: Si el colaborador tiene cuenta de banco temporal (ID 23), eliminarla con forceDelete.
     */
    private function eliminarCuentaTemporalDelColaborador(?int $colaboradorId): void
    {
        if (! $colaboradorId) {
            return;
        }

        CuentaBancaria::query()
            ->where('colaborador_id', $colaboradorId)
            ->where('banco_id', self::BANCO_TEMPORAL_ID)
            ->each(fn (CuentaBancaria $cuenta) => $cuenta->forceDelete());
    }

    /**
     * BL: Rechaza una cuenta bancaria.
     * - Si reenviar=true: reenvía a verificación (SIN_VERIFICAR + enviado_verificacion=false)
     * - Si reenviar=false: marca como rechazada, elimina si no es nómina, reasigna adeudos
     */
    public function rechazarCuenta(CuentaBancaria $cuenta, bool $reenviar = false): void
    {
        if ($reenviar) {
            $this->reenviarCuenta($cuenta);

            return;
        }

        if (! $cuenta->estado->estaSinVerificar()) {
            throw ValidationException::withMessages([
                'cuenta' => "La cuenta #{$cuenta->numero} no está pendiente de verificación.",
            ]);
        }

        DB::transaction(function () use ($cuenta): void {
            $cuenta->marcarComoRechazada();

            $this->reasignarAdeudosPendientes($cuenta);

            // BL: Si no es cuenta de nómina, eliminarla (soft delete)
            if (! $cuenta->es_nomina) {
                $cuenta->delete();
            }
        });

        $this->notificarResultadoVerificacion($cuenta, 'rechazo');
    }

    /**
     * BL: Envía notificación push al colaborador sobre el resultado de verificación.
     *
     * Validaciones:
     * - El colaborador debe existir
     * - La empresa debe existir
     * - La empresa debe tener incluido el motivo de notificación
     * - La empresa debe tener credenciales OneSignal configuradas
     */
    private function notificarResultadoVerificacion(CuentaBancaria $cuenta, string $tipo): void
    {
        $colaborador = $cuenta->colaborador;
        if (! $colaborador) {
            return;
        }

        $empresa = $colaborador->empresa;
        if (! $empresa) {
            return;
        }

        $motivoId = $tipo === 'validacion'
            ? self::MOTIVO_NOTIFICACION_VALIDACION
            : self::MOTIVO_NOTIFICACION_RECHAZO;

        // BL: Verificar si la empresa tiene incluido este tipo de notificación
        $tieneMotivo = $empresa->notificacionesIncluidas()
            ->where('notificacion_incluida_id', $motivoId)
            ->exists();

        if (! $tieneMotivo) {
            return;
        }

        // BL: Verificar si la empresa tiene credenciales OneSignal
        if (! $empresa->getOneSignalCredentials()) {
            return;
        }

        $esValidacion = $tipo === 'validacion';
        $ultimosCuatro = substr($cuenta->numero, -4);

        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $empresa->id,
            'titulo' => $esValidacion
                ? 'Validación de cuenta EXITOSA'
                : 'RECHAZO en validación de cuenta',
            'mensaje' => $esValidacion
                ? "Tu cuenta terminada en {$ultimosCuatro} ha sido verificada correctamente."
                : "Tu cuenta terminada en {$ultimosCuatro} fue rechazada en validación.",
            'estado' => EstadoNotificacionPush::ENVIANDO,
            'filtros' => [
                'destinatarios' => [
                    'select_all' => false,
                    'manual_activation' => [$colaborador->id],
                ],
            ],
            'total_destinatarios' => 1,
        ]);

        app(ResolverDestinatariosService::class)->persistirDestinatarios($notificacion);

        EnviarNotificacionPushJob::dispatch($notificacion);
    }

    /**
     * BL: Reenvía una cuenta rechazada o sin verificar a verificación.
     */
    private function reenviarCuenta(CuentaBancaria $cuenta): void
    {
        if (! $cuenta->puedeReenviarse() && ! $cuenta->estado->estaSinVerificar()) {
            throw ValidationException::withMessages([
                'cuenta' => "La cuenta #{$cuenta->numero} no puede reenviarse.",
            ]);
        }

        $cuenta->reenviarAVerificacion();
    }

    /**
     * BL: Reasigna adeudos pendientes con codigo_razon 01 o 03
     * a otra cuenta verificada del colaborador.
     */
    private function reasignarAdeudosPendientes(CuentaBancaria $cuentaRechazada): void
    {
        if (! $cuentaRechazada->colaborador_id) {
            return;
        }

        $cuentaAlternativa = CuentaBancaria::query()
            ->where('colaborador_id', $cuentaRechazada->colaborador_id)
            ->where('id', '!=', $cuentaRechazada->id)
            ->verificadas()
            ->first();

        if (! $cuentaAlternativa) {
            return;
        }

        // BL: Solo adeudos cuyo último intento tenga codigo_razon en [1, 3]
        CuentaPorCobrar::query()
            ->where('cuenta_bancaria_id', $cuentaRechazada->id)
            ->where('estado', 'PENDIENTE')
            ->whereHas('intentosCobro', function ($query): void {
                $query->whereIn('codigo_razon', self::CODIGOS_RAZON_REASIGNACION)
                    ->whereRaw('id = (SELECT MAX(id) FROM intentos_cobro WHERE cuenta_por_cobrar_id = cuentas_por_cobrar.id)');
            })
            ->update(['cuenta_bancaria_id' => $cuentaAlternativa->id]);
    }

    /**
     * BL: Obtiene cuentas pendientes de envío a verificación.
     * Aplica filtros: antigüedad >= 3 meses, no banco temporal, hora de bloqueo.
     *
     * @return Collection<int, CuentaBancaria>
     */
    public function obtenerCuentasPendientesDeEnvio(): Collection
    {
        if ($this->esHoraDeBloqueo()) {
            return collect();
        }

        return CuentaBancaria::query()
            ->sinVerificar()
            ->pendientesDeEnvio()
            ->where('banco_id', '!=', self::BANCO_TEMPORAL_ID)
            ->whereHas('colaborador', function ($query): void {
                $query->whereNull('deleted_at')
                    ->where('fecha_ingreso', '<=', now()->subMonths(self::ANTIGUEDAD_MINIMA_MESES));
            })
            ->with(['colaborador', 'banco'])
            ->limit(self::LIMITE_CUENTAS_POR_LOTE)
            ->get();
    }

    /**
     * BL: Verifica si es hora de bloqueo de envío (18:00-18:59 hora Guatemala).
     */
    private function esHoraDeBloqueo(): bool
    {
        return Carbon::now('America/Guatemala')->hour === self::HORA_BLOQUEO_ENVIO;
    }

    /**
     * BL: Marca un conjunto de cuentas como enviadas a verificación.
     *
     * @param  Collection<int, CuentaBancaria>  $cuentas
     */
    public function marcarComoEnviadas(Collection $cuentas): void
    {
        CuentaBancaria::query()
            ->whereIn('id', $cuentas->pluck('id'))
            ->update(['enviado_verificacion' => true]);
    }

    /**
     * BL: Procesa resultados de verificación masiva.
     *
     * @param  array<int, array{numero: string, resultado: string, reenviar?: bool}>  $resultados
     * @return array{validadas: int, rechazadas: int, reenviadas: int, errores: list<string>}
     */
    public function procesarResultadosMasivos(array $resultados): array
    {
        $resumen = [
            'validadas' => 0,
            'rechazadas' => 0,
            'reenviadas' => 0,
            'errores' => [],
        ];

        foreach ($resultados as $resultado) {
            try {
                $cuenta = CuentaBancaria::query()
                    ->where('numero', $resultado['numero'])
                    ->sinVerificar()
                    ->whereHas('colaborador', fn ($q) => $q->whereNull('deleted_at'))
                    ->first();

                if (! $cuenta) {
                    $resumen['errores'][] = "Cuenta {$resultado['numero']} no encontrada o no está pendiente.";

                    continue;
                }

                if ($resultado['resultado'] === 'Valida') {
                    $this->validarCuenta($cuenta);
                    $resumen['validadas']++;
                } else {
                    $reenviar = $resultado['reenviar'] ?? false;
                    $this->rechazarCuenta($cuenta, $reenviar);

                    if ($reenviar) {
                        $resumen['reenviadas']++;
                    } else {
                        $resumen['rechazadas']++;
                    }
                }
            } catch (ValidationException $e) {
                $resumen['errores'][] = $e->getMessage();
            } catch (\Exception $e) {
                $resumen['errores'][] = "Error procesando cuenta {$resultado['numero']}: {$e->getMessage()}";
            }
        }

        return $resumen;
    }

    /**
     * BL: Prepara el payload de cuentas para envío a STP.
     *
     * @param  Collection<int, CuentaBancaria>  $cuentas
     * @return array<int, array{date: string, transferId: int, institucionContraparte: int, bank_code: string, account: string, amount: int}>
     */
    public function prepararPayloadSTP(Collection $cuentas): array
    {
        return $cuentas->map(fn (CuentaBancaria $cuenta): array => [
            'date' => now()->format('Y-m-d'),
            'transferId' => $cuenta->id,
            'institucionContraparte' => 90646,
            'bank_code' => $cuenta->banco?->codigo ?? '',
            'account' => $cuenta->numero,
            'amount' => 0,
        ])->toArray();
    }

    public function contarPendientes(): int
    {
        return CuentaBancaria::query()->sinVerificar()->count();
    }

    public function contarPendientesDeEnvio(): int
    {
        return CuentaBancaria::query()
            ->sinVerificar()
            ->pendientesDeEnvio()
            ->where('banco_id', '!=', self::BANCO_TEMPORAL_ID)
            ->count();
    }
}
