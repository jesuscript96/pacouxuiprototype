<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AreaGeneral;
use App\Models\CentroPago;
use App\Models\Departamento;
use App\Models\DepartamentoGeneral;
use App\Models\Empresa;
use App\Models\Ocupacion;
use App\Models\Puesto;
use App\Models\PuestoGeneral;
use App\Models\Region;
use App\Models\Ubicacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Crea 6 registros por tabla solicitada (3 por cada una de las 2 primeras empresas por ID),
 * con nombres del tipo: «Área general 1 - Empresa {id}» … «Área general 3 - Empresa {id}».
 *
 * Requisito: al menos 2 filas en `empresas` (se usan las dos menores por id).
 */
class EmpresaCatalogosDemostrativosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $empresas = Empresa::query()->orderBy('id')->limit(2)->get();
        if ($empresas->count() < 2) {
            $this->command?->error('EmpresaCatalogosDemostrativosSeeder: se requieren al menos 2 empresas en la base de datos.');

            return;
        }

        $ocupacion = Ocupacion::query()->first();
        if ($ocupacion === null) {
            $ocupacion = Ocupacion::query()->create(['descripcion' => 'Ocupación catálogo demostrativo']);
        }

        /** @var array<int, list<DepartamentoGeneral>> */
        $departamentosGeneralesPorEmpresa = [];
        /** @var array<int, list<AreaGeneral>> */
        $areasGeneralesPorEmpresa = [];
        /** @var array<int, list<PuestoGeneral>> */
        $puestosGeneralesPorEmpresa = [];

        foreach ($empresas as $empresa) {
            $eid = (int) $empresa->getKey();
            $departamentosGeneralesPorEmpresa[$eid] = [];
            $areasGeneralesPorEmpresa[$eid] = [];
            $puestosGeneralesPorEmpresa[$eid] = [];

            for ($n = 1; $n <= 3; $n++) {
                $departamentosGeneralesPorEmpresa[$eid][] = DepartamentoGeneral::query()->firstOrCreate(
                    ['nombre' => "Departamento General {$n} - Empresa {$eid}"]
                );

                $areasGeneralesPorEmpresa[$eid][] = AreaGeneral::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Área general {$n} - Empresa {$eid}",
                    ]
                );

                $puestosGeneralesPorEmpresa[$eid][] = PuestoGeneral::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Puesto general {$n} - Empresa {$eid}",
                    ]
                );
            }
        }

        foreach ($empresas as $empresa) {
            $eid = (int) $empresa->getKey();
            for ($n = 1; $n <= 3; $n++) {
                $idx = $n - 1;
                $areaGeneral = $areasGeneralesPorEmpresa[$eid][$idx];

                Area::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Área {$n} - Empresa {$eid}",
                    ],
                    ['area_general_id' => $areaGeneral->id]
                );

                CentroPago::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Centro de pago {$n} - Empresa {$eid}",
                    ],
                    [
                        'registro_patronal' => null,
                        'direccion_imss' => null,
                    ]
                );

                $dg = $departamentosGeneralesPorEmpresa[$eid][$idx];
                Departamento::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Departamento {$n} - Empresa {$eid}",
                    ],
                    ['departamento_general_id' => $dg->id]
                );

                $pg = $puestosGeneralesPorEmpresa[$eid][$idx];
                Puesto::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Puesto {$n} - Empresa {$eid}",
                    ],
                    [
                        'puesto_general_id' => $pg->id,
                        'ocupacion_id' => $ocupacion->id,
                        'area_general_id' => $areaGeneral->id,
                    ]
                );

                Region::query()->firstOrCreate([
                    'empresa_id' => $eid,
                    'nombre' => "Región {$n} - Empresa {$eid}",
                ]);

                Ubicacion::query()->firstOrCreate(
                    [
                        'empresa_id' => $eid,
                        'nombre' => "Ubicación {$n} - Empresa {$eid}",
                    ],
                    [
                        'cp' => sprintf('%05d', min(99999, $eid * 100 + $n)),
                        'mostrar_modal_calendly' => true,
                        'registro_patronal_sucursal' => null,
                        'direccion_imss' => null,
                    ]
                );
            }
        }

        $this->command?->info('EmpresaCatalogosDemostrativosSeeder: catálogos creados o ya existentes (idempotente por nombre + empresa).');
    }
}
