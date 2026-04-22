<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdelantoNomina;
use App\Models\Recarga;
use App\Models\ServicioPago;
use App\Models\Transaccion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportesInternosMovimientosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $transacciones = Transaccion::query()
            ->whereIn('tipo', ['ADELANTO DE NOMINA', 'RECARGA', 'PAGO DE SERVICIO'])
            ->with([
                'adelantoNomina',
                'recarga',
                'servicioPago',
                'estadoCuenta.cuentasPorCobrar.cuentaBancaria',
            ])
            ->get();

        DB::transaction(function () use ($transacciones): void {
            foreach ($transacciones as $transaccion) {
                $cuenta = $transaccion->estadoCuenta?->cuentasPorCobrar?->first();
                $cuentaBancaria = $cuenta?->cuentaBancaria;

                if ($cuentaBancaria === null) {
                    continue;
                }

                if ($transaccion->tipo === 'ADELANTO DE NOMINA' && $transaccion->adelantoNomina === null) {
                    AdelantoNomina::query()->create([
                        'transaccion_id' => $transaccion->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'id_transferencia' => 'TRF-BF-'.$transaccion->id,
                        'centro_costo' => 'CENTRO DISPERSION BACKFILL',
                        'clave_seguimiento' => 'TK-BF-'.$transaccion->id,
                    ]);
                }

                if ($transaccion->tipo === 'RECARGA' && $transaccion->recarga === null) {
                    Recarga::query()->create([
                        'id_producto_externo' => 'RECARGA-BF-'.$transaccion->id,
                        'id_cuenta_externo' => (string) $cuentaBancaria->numero,
                        'transaccion_id' => $transaccion->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'centro_costo' => 'CENTRO DISPERSION BACKFILL',
                        'codigo_operacion' => 'OP-BF-'.$transaccion->id,
                    ]);
                }

                if ($transaccion->tipo === 'PAGO DE SERVICIO' && $transaccion->servicioPago === null) {
                    ServicioPago::query()->create([
                        'id_producto_externo' => 'SERVICIO-BF-'.$transaccion->id,
                        'id_cuenta_externo' => (string) $cuentaBancaria->numero,
                        'modo_pago' => 'SALDO',
                        'transaccion_id' => $transaccion->id,
                        'cuenta_bancaria_id' => $cuentaBancaria->id,
                        'nombre_producto' => 'Servicio backfill '.$transaccion->id,
                        'tipo' => 'PAGO',
                        'centro_costo' => 'CENTRO DISPERSION BACKFILL',
                        'codigo_operacion' => 'OP-BF-'.$transaccion->id,
                    ]);
                }
            }
        });
    }
}
