<?php

declare(strict_types=1);

namespace App\Services\ReportesInternos;

use App\Models\CuentaPorCobrar;
use App\Models\Transaccion;
use App\Models\TransaccionExcluida;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * BL: Réplica de la lógica de filtros y agregación del legacy ReceivableAccountsController / InternalReport exports.
 */
class ReporteCierreMesService
{
    public const string ESTADO_CUENTA_INACTIVO = 'INACTIVO';

    public const string ESTADO_TRANSACCION_EXITOSA = 'EXITOSA';

    public const string TIPO_PAGO_SALDO_SISTEMA = 'SALDO DEL SISTEMA';

    public const string TIPO_PENALIZACION = 'PENALIZACION';

    /**
     * @param  list<int|string>  $ubicacionIds
     * @param  list<int|string>  $anios
     * @param  list<int|string>  $meses  1–12
     * @return Collection<int, CuentaPorCobrar>
     */
    public function cuentasFiltradas(int $empresaId, array $ubicacionIds, array $anios, array $meses): Collection
    {
        $idsColaboradores = User::query()
            ->colaboradoresDeEmpresa($empresaId)
            ->pluck('id')
            ->all();

        $query = CuentaPorCobrar::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('user_id', $idsColaboradores)
            ->whereHas('estadoCuenta', fn (Builder $q) => $q->where('estado', self::ESTADO_CUENTA_INACTIVO));

        if ($ubicacionIds !== []) {
            $query->whereIn('ubicacion_id', array_map('intval', $ubicacionIds));
        }

        if ($anios !== [] || $meses !== []) {
            $query->whereNotNull('fecha_confirmacion_pago');
        }

        if ($anios !== []) {
            $aniosInt = array_map('intval', $anios);
            $query->where(function (Builder $q) use ($aniosInt): void {
                foreach ($aniosInt as $anio) {
                    $q->orWhereYear('fecha_confirmacion_pago', $anio);
                }
            });
        }

        if ($meses !== []) {
            $mesesInt = array_map('intval', $meses);
            $query->where(function (Builder $q) use ($mesesInt): void {
                foreach ($mesesInt as $mes) {
                    $q->orWhereMonth('fecha_confirmacion_pago', $mes);
                }
            });
        }

        return $query->with([
            'estadoCuenta.transacciones',
            'empresa',
            'ubicacion',
            'intentosCobro',
            'hijos',
            'transaccionesExcluidas',
            'padre',
        ])->get();
    }

    public function etiquetaTipoConfirmacion(?string $tipo): string
    {
        return match ($tipo) {
            'MANUAL' => 'Manual',
            'BANK_CONFIRMATION' => 'Bancaria',
            'PAYMENT_GATEWAY' => 'Pasarela de pagos',
            'BELVO' => 'Belvo',
            default => 'Sin definir',
        };
    }

    /**
     * @return array{0: string, 1: int, 2: int} fecha ISO, año, mes
     */
    public function resolverFechaConfirmacionPago(CuentaPorCobrar $cuenta): array
    {
        $intento = $cuenta->intentosCobro->first(fn ($i) => (int) $i->codigo_razon === 0);

        if ($intento !== null && $intento->fecha_liquidacion !== null && $cuenta->tipo_confirmacion_pago !== 'MANUAL') {
            $fecha = Carbon::parse($intento->fecha_liquidacion)->toDateTimeString();
        } else {
            $fecha = $cuenta->fecha_confirmacion_pago !== null
                ? Carbon::parse($cuenta->fecha_confirmacion_pago)->toDateTimeString()
                : '';
        }

        if ($fecha === '') {
            return ['', 0, 0];
        }

        $c = Carbon::parse($fecha);

        return [$fecha, $c->year, $c->month];
    }

    /**
     * Transacciones «SALDO DEL SISTEMA» exitosas con reglas de excluidas del legacy.
     *
     * @return \Illuminate\Support\Collection<int, Transaccion>
     */
    public function transaccionesParaDetalle(CuentaPorCobrar $cuenta): \Illuminate\Support\Collection
    {
        $estadoCuenta = $cuenta->estadoCuenta;
        if ($estadoCuenta === null) {
            return collect();
        }

        $query = Transaccion::query()
            ->where('estado_cuenta_id', $estadoCuenta->id)
            ->where('estado', self::ESTADO_TRANSACCION_EXITOSA)
            ->where('tipo_pago', self::TIPO_PAGO_SALDO_SISTEMA)
            ->with(['adelantoNomina', 'recarga', 'servicioPago']);

        if ($cuenta->hijos->isNotEmpty() && $cuenta->transaccionesExcluidas->isNotEmpty()) {
            $ids = $cuenta->transaccionesExcluidas->pluck('transaccion_id')->all();
            $query->whereIn('id', $ids);
        }

        if ($cuenta->parent_id !== null) {
            $idsPadre = TransaccionExcluida::query()
                ->where('cuenta_por_cobrar_id', $cuenta->parent_id)
                ->pluck('transaccion_id')
                ->all();
            if ($idsPadre !== []) {
                $query->whereNotIn('id', $idsPadre);
            }
        }

        return $query->get();
    }

    public function centroCostoDispersion(Transaccion $transaccion): string
    {
        $tipo = mb_strtoupper(trim((string) $transaccion->tipo));

        $centroCosto = match ($tipo) {
            'ADELANTO DE NOMINA' => $transaccion->adelantoNomina?->centro_costo,
            'RECARGA' => $transaccion->recarga?->centro_costo,
            'PAGO DE SERVICIO' => $transaccion->servicioPago?->centro_costo,
            default => null,
        };

        if ($centroCosto === null || trim($centroCosto) === '') {
            return '';
        }

        if (mb_strtoupper(trim($centroCosto)) === mb_strtoupper('TECNOLOGÍA EN BENEFICIOS MÉXICO SA DE CV')) {
            return 'PACO';
        }

        return $centroCosto;
    }

    /**
     * @param  list<int|string>  $aniosFiltro
     * @param  list<int|string>  $mesesFiltro
     */
    public function pasaFiltroTemporal(int $anio, int $mes, array $aniosFiltro, array $mesesFiltro): bool
    {
        $aniosFiltro = array_map('intval', $aniosFiltro);
        $mesesFiltro = array_map('intval', $mesesFiltro);

        if ($aniosFiltro !== [] && $mesesFiltro !== []) {
            return in_array($anio, $aniosFiltro, true) && in_array($mes, $mesesFiltro, true);
        }
        if ($aniosFiltro !== []) {
            return in_array($anio, $aniosFiltro, true);
        }
        if ($mesesFiltro !== []) {
            return in_array($mes, $mesesFiltro, true);
        }

        return true;
    }
}
