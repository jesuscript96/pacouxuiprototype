<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdelantoNomina;
use App\Models\Area;
use App\Models\Banco;
use App\Models\ComprobanteTxtProcesado;
use App\Models\CuentaBancaria;
use App\Models\CuentaPorCobrar;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\EstadoCuenta;
use App\Models\IntentoCobro;
use App\Models\PenalizacionExclusiva;
use App\Models\Puesto;
use App\Models\Recarga;
use App\Models\RetencionNomina;
use App\Models\ServicioPago;
use App\Models\Transaccion;
use App\Models\TransaccionExcluida;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesInternosDemostrativosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $banco = Banco::query()->first();
        if (! $banco instanceof Banco) {
            $this->command?->error('ReportesInternosDemostrativosSeeder: no hay bancos en la base de datos.');

            return;
        }

        $usuarios = User::query()
            ->where(function ($query): void {
                $query->whereNull('tipo')
                    ->orWhereJsonDoesntContain('tipo', 'administrador');
            })
            ->with(['empresas'])
            ->orderBy('id')
            ->get();

        if ($usuarios->isEmpty()) {
            $this->command?->warn('ReportesInternosDemostrativosSeeder: no hay usuarios elegibles para poblar datos.');

            return;
        }

        $comprobante = ComprobanteTxtProcesado::query()->create([
            'nombre' => 'comprobante_reporte_interno_2026.txt',
        ]);

        $creados = 0;

        DB::transaction(function () use ($usuarios, $banco, $comprobante, &$creados): void {
            foreach ($usuarios as $indice => $usuario) {
                $empresaId = $this->resolverEmpresaId($usuario);
                if ($empresaId === null) {
                    continue;
                }

                $yaExiste = CuentaPorCobrar::query()
                    ->where('user_id', $usuario->id)
                    ->where('empresa_id', $empresaId)
                    ->whereYear('fecha_confirmacion_pago', 2026)
                    ->exists();

                if ($yaExiste) {
                    continue;
                }

                $catalogos = $this->catalogosEmpresa($empresaId);
                if ($catalogos['ubicacion_ids'] === []) {
                    continue;
                }

                $fechaConfirmacion = $this->fechaAleatoria2026($usuario->id, $indice);
                $fechaPago = $fechaConfirmacion->copy()->subDays(2);
                $tipoConfirmacion = $indice % 2 === 0 ? 'BANK_CONFIRMATION' : 'MANUAL';

                $cuentaBancaria = CuentaBancaria::query()->create([
                    'numero' => str_pad((string) (1000000000 + $usuario->id), 10, '0', STR_PAD_LEFT),
                    'tipo' => 'DEBITO',
                    'alias' => 'Cuenta reporte '.$usuario->id,
                    'estado' => 'ACTIVA',
                    'banco_id' => $banco->id,
                    'user_id' => $usuario->id,
                    'es_nomina' => true,
                    'envio_verificacion' => 'APROBADO',
                ]);

                $estadoCuenta = EstadoCuenta::query()->create([
                    'desde' => $fechaConfirmacion->copy()->startOfMonth()->toDateTimeString(),
                    'hasta' => $fechaConfirmacion->copy()->endOfMonth()->toDateTimeString(),
                    'saldo' => 1250.00,
                    'saldo_sin_comision' => 1200.00,
                    'estado' => 'INACTIVO',
                    'periodicidad_pago' => 'MENSUAL',
                    'user_id' => $usuario->id,
                    'tipo_comision' => 'FIJA',
                    'monto_comision' => 50.00,
                ]);

                $cuenta = CuentaPorCobrar::query()->create([
                    'estado' => $indice % 3 === 0 ? 'PAGADO PARCIALMENTE' : 'PAGADO',
                    'debe' => 1250.00,
                    'estado_cuenta_id' => $estadoCuenta->id,
                    'cuenta_bancaria_id' => $cuentaBancaria->id,
                    'empresa_id' => $empresaId,
                    'user_id' => $usuario->id,
                    'fecha_pago' => $fechaPago->toDateTimeString(),
                    'fecha_confirmacion_pago' => $fechaConfirmacion->toDateTimeString(),
                    'comentarios' => 'Registro demostrativo para reporte interno',
                    'tipo_confirmacion_pago' => $tipoConfirmacion,
                    'comisiones_bancarias' => 12.50,
                    'periodicidad_pago' => 'MENSUAL',
                    'ubicacion_id' => $catalogos['ubicacion_ids'][0] ?? null,
                    'puesto_id' => $catalogos['puesto_ids'][0] ?? null,
                    'departamento_id' => $catalogos['departamento_ids'][0] ?? null,
                    'area_id' => $catalogos['area_ids'][0] ?? null,
                    'centro_costo' => 'CC-'.str_pad((string) $empresaId, 3, '0', STR_PAD_LEFT),
                ]);

                IntentoCobro::query()->create([
                    'codigo_razon' => 0,
                    'referencia_numerica_emisor' => 'REF-'.$cuenta->id,
                    'descripcion' => 'Cobro exitoso de demostracion',
                    'fecha_liquidacion' => $fechaConfirmacion->copy()->addHour()->toDateTimeString(),
                    'cuenta_bancaria_id' => $cuentaBancaria->id,
                    'cuenta_por_cobrar_id' => $cuenta->id,
                    'monto' => 1250.00,
                    'comprobante_txt_procesado_id' => $comprobante->id,
                    'es_recargo' => false,
                    'estado_recargo' => 'NO_APLICA',
                    'monto_cobrado' => 1250.00,
                ]);

                $tipos = ['ADELANTO DE NOMINA', 'RECARGA', 'PAGO DE SERVICIO'];
                $tipoMovimiento = $tipos[$indice % 3];
                $centroCostoDispersion = $indice % 5 === 0
                    ? 'TECNOLOGÍA EN BENEFICIOS MÉXICO SA DE CV'
                    : 'CENTRO DISPERSION '.($indice + 1);

                $txProducto = Transaccion::query()->create([
                    'fecha' => $fechaConfirmacion->toDateTimeString(),
                    'tipo' => $tipoMovimiento,
                    'monto' => 1000.00,
                    'comision' => 100.00,
                    'estado_cuenta_id' => $estadoCuenta->id,
                    'estado' => 'EXITOSA',
                    'tipo_pago' => 'SALDO DEL SISTEMA',
                ]);

                if ($tipoMovimiento === 'ADELANTO DE NOMINA') {
                    AdelantoNomina::query()->create([
                        'transaccion_id' => $txProducto->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'id_transferencia' => 'TRF-'.$txProducto->id,
                        'centro_costo' => $centroCostoDispersion,
                        'clave_seguimiento' => 'TK-'.$txProducto->id,
                    ]);
                } elseif ($tipoMovimiento === 'RECARGA') {
                    Recarga::query()->create([
                        'id_producto_externo' => 'RECARGA-'.$txProducto->id,
                        'id_cuenta_externo' => (string) $cuentaBancaria->numero,
                        'transaccion_id' => $txProducto->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'centro_costo' => $centroCostoDispersion,
                        'codigo_operacion' => 'OP-'.$txProducto->id,
                    ]);
                } else {
                    ServicioPago::query()->create([
                        'id_producto_externo' => 'SERVICIO-'.$txProducto->id,
                        'id_cuenta_externo' => (string) $cuentaBancaria->numero,
                        'modo_pago' => 'SALDO',
                        'transaccion_id' => $txProducto->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'nombre_producto' => 'Servicio demo '.$txProducto->id,
                        'tipo' => 'PAGO',
                        'centro_costo' => $centroCostoDispersion,
                        'codigo_operacion' => 'OP-'.$txProducto->id,
                    ]);
                }

                $txPenalizacion = Transaccion::query()->create([
                    'fecha' => $fechaConfirmacion->toDateTimeString(),
                    'tipo' => 'PENALIZACION',
                    'monto' => 150.00,
                    'comision' => 0.00,
                    'estado_cuenta_id' => $estadoCuenta->id,
                    'estado' => 'EXITOSA',
                    'tipo_pago' => 'SALDO DEL SISTEMA',
                ]);

                PenalizacionExclusiva::query()->create([
                    'transaccion_id' => $txPenalizacion->id,
                    'cuenta_por_cobrar_id' => $cuenta->id,
                ]);

                if ($indice % 2 === 0) {
                    $hija = CuentaPorCobrar::query()->create([
                        'estado' => 'PAGADO',
                        'debe' => 300.00,
                        'estado_cuenta_id' => $estadoCuenta->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'empresa_id' => $empresaId,
                        'user_id' => $usuario->id,
                        'fecha_pago' => $fechaPago->toDateTimeString(),
                        'fecha_confirmacion_pago' => $fechaConfirmacion->toDateTimeString(),
                        'tipo_confirmacion_pago' => 'BANK_CONFIRMATION',
                        'parent_id' => $cuenta->id,
                        'ubicacion_id' => $catalogos['ubicacion_ids'][0] ?? null,
                    ]);

                    TransaccionExcluida::query()->create([
                        'transaccion_id' => $txProducto->id,
                        'cuenta_por_cobrar_id' => $cuenta->id,
                    ]);

                    RetencionNomina::query()->create([
                        'periodicidad_pago' => 'MENSUAL',
                        'estado' => 'ACTIVA',
                        'cuenta_por_cobrar_id' => $hija->id,
                        'empresa_id' => $empresaId,
                        'user_id' => $usuario->id,
                    ]);
                } else {
                    RetencionNomina::query()->create([
                        'periodicidad_pago' => 'MENSUAL',
                        'estado' => 'ACTIVA',
                        'cuenta_por_cobrar_id' => $cuenta->id,
                        'empresa_id' => $empresaId,
                        'user_id' => $usuario->id,
                    ]);
                }

                $creados++;
            }
        });

        $this->command?->info("ReportesInternosDemostrativosSeeder: {$creados} usuarios poblados con datos 2026.");
    }

    private function resolverEmpresaId(User $usuario): ?int
    {
        if ($usuario->empresa_id !== null) {
            return (int) $usuario->empresa_id;
        }

        $empresa = $usuario->empresas->first();

        return $empresa instanceof Empresa ? (int) $empresa->id : null;
    }

    /**
     * @return array{
     *     ubicacion_ids: list<int>,
     *     departamento_ids: list<int>,
     *     area_ids: list<int>,
     *     puesto_ids: list<int>
     * }
     */
    private function catalogosEmpresa(int $empresaId): array
    {
        return [
            'ubicacion_ids' => Ubicacion::query()->where('empresa_id', $empresaId)->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            'departamento_ids' => Departamento::query()->where('empresa_id', $empresaId)->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            'area_ids' => Area::query()->where('empresa_id', $empresaId)->pluck('id')->map(fn ($id): int => (int) $id)->all(),
            'puesto_ids' => Puesto::query()->where('empresa_id', $empresaId)->pluck('id')->map(fn ($id): int => (int) $id)->all(),
        ];
    }

    private function fechaAleatoria2026(int $usuarioId, int $indice): Carbon
    {
        $mes = (($usuarioId + $indice) % 12) + 1;
        $dia = (($usuarioId + ($indice * 3)) % 26) + 1;

        return Carbon::create(2026, $mes, $dia, 10, 30, 0);
    }
}
