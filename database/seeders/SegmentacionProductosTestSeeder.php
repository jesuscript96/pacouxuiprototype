<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\AreaGeneral;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Ocupacion;
use App\Models\Puesto;
use App\Models\PuestoGeneral;
use App\Models\Region;
use App\Models\Ubicacion;
use App\Services\ColaboradorService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SegmentacionProductosTestSeeder extends Seeder
{
    private const EMPRESA_ID = 1;

    /**
     * Inserta catálogos (Ubicaciones, Departamentos, Áreas, Puestos, Regiones)
     * y 10 usuarios (tipo colaborador) con edades, departamentos, etc. variados para empresa 1,
     * para probar la segmentación de productos (ej. producto 6).
     */
    public function run(): void
    {
        $empresa = Empresa::find(self::EMPRESA_ID);
        if (! $empresa) {
            $this->command->error('No existe la empresa con ID '.self::EMPRESA_ID.'. Ejecuta antes EmpresaEjemploSeeder o crea la empresa.');

            return;
        }

        $productoId = 6;
        if (! $empresa->productos()->where('productos.id', $productoId)->exists()) {
            $empresa->productos()->attach($productoId, ['desde' => 1]);
            $this->command->info("Producto {$productoId} asignado a la empresa.");
        }

        $this->command->info('Creando catálogos para empresa '.self::EMPRESA_ID.'...');

        $ubicaciones = $this->crearUbicaciones($empresa);
        $departamentos = $this->crearDepartamentos($empresa);
        $areas = $this->crearAreas($empresa);
        $puestos = $this->crearPuestos($empresa);
        $regiones = $this->crearRegiones($empresa);

        $this->command->info('Creando 10 usuarios colaborador de prueba...');
        $this->crearColaboradores($empresa, $ubicaciones, $departamentos, $areas, $puestos, $regiones);

        $this->command->info('Listo. Puedes probar la segmentación en Admin > Segmentación de Productos (empresa 1, producto 6).');
    }

    /**
     * @return array<int, Ubicacion>
     */
    private function crearUbicaciones(Empresa $empresa): array
    {
        $rows = [
            ['nombre' => 'Oficina Central', 'cp' => '01000'],
            ['nombre' => 'Sucursal Norte', 'cp' => '02000'],
            ['nombre' => 'Sucursal Sur', 'cp' => '03000'],
        ];
        $out = [];
        foreach ($rows as $r) {
            $u = Ubicacion::withoutEvents(fn () => Ubicacion::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'nombre' => $r['nombre'],
                ],
                [
                    'cp' => $r['cp'],
                    'mostrar_modal_calendly' => true,
                ]
            ));
            $out[$u->id] = $u;
        }

        return $out;
    }

    /**
     * @return array<int, Departamento>
     */
    private function crearDepartamentos(Empresa $empresa): array
    {
        $nombres = ['Ventas', 'Recursos Humanos', 'Operaciones'];
        $out = [];
        foreach ($nombres as $nombre) {
            $d = Departamento::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $nombre],
                []
            );
            $out[$d->id] = $d;
        }

        return $out;
    }

    /**
     * @return array<int, Area>
     */
    private function crearAreas(Empresa $empresa): array
    {
        $ag = AreaGeneral::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Área General Ejemplo'],
            []
        );
        $nombres = ['Área Comercial', 'Área Administrativa'];
        $out = [];
        foreach ($nombres as $nombre) {
            $a = Area::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'nombre' => $nombre,
                ],
                ['area_general_id' => $ag->id]
            );
            $out[$a->id] = $a;
        }

        return $out;
    }

    /**
     * @return array<int, Puesto>
     */
    private function crearPuestos(Empresa $empresa): array
    {
        $ag = AreaGeneral::where('empresa_id', $empresa->id)->first();
        if (! $ag) {
            $ag = AreaGeneral::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => 'Área General Ejemplo'],
                []
            );
        }
        $pg = PuestoGeneral::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Puesto General Ejemplo'],
            []
        );
        $ocupacion = Ocupacion::query()->first();
        if (! $ocupacion) {
            $ocupacion = Ocupacion::create(['descripcion' => 'Empleado general']);
        }
        $nombres = ['Asistente', 'Analista', 'Coordinador'];
        $out = [];
        foreach ($nombres as $nombre) {
            $p = Puesto::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'nombre' => $nombre,
                ],
                [
                    'puesto_general_id' => $pg->id,
                    'ocupacion_id' => $ocupacion->id,
                    'area_general_id' => $ag->id,
                ]
            );
            $out[$p->id] = $p;
        }

        return $out;
    }

    /**
     * @return array<int, Region>
     */
    private function crearRegiones(Empresa $empresa): array
    {
        $nombres = ['Centro', 'Norte'];
        $out = [];
        foreach ($nombres as $nombre) {
            $r = Region::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $nombre],
                []
            );
            $out[$r->id] = $r;
        }

        return $out;
    }

    /**
     * @param  array<int, Ubicacion>  $ubicaciones
     * @param  array<int, Departamento>  $departamentos
     * @param  array<int, Area>  $areas
     * @param  array<int, Puesto>  $puestos
     * @param  array<int, Region>  $regiones
     */
    private function crearColaboradores(
        Empresa $empresa,
        array $ubicaciones,
        array $departamentos,
        array $areas,
        array $puestos,
        array $regiones
    ): void {
        $uIds = array_values(array_map(fn ($u) => $u->id, $ubicaciones));
        $dIds = array_values(array_map(fn ($d) => $d->id, $departamentos));
        $aIds = array_values(array_map(fn ($a) => $a->id, $areas));
        $pIds = array_values(array_map(fn ($p) => $p->id, $puestos));
        $rIds = array_values(array_map(fn ($r) => $r->id, $regiones));

        $colaboradorService = app(ColaboradorService::class);

        // 10 colaboradores: edades variadas (22-55), fechas de ingreso variadas (1-120 meses atrás), géneros y catálogos variados
        $datos = [
            ['nombre' => 'Ana', 'apellido' => 'García López', 'edad' => 28, 'meses_empresa' => 12, 'genero' => 'Femenino'],
            ['nombre' => 'Carlos', 'apellido' => 'Martínez Ruiz', 'edad' => 35, 'meses_empresa' => 24, 'genero' => 'Masculino'],
            ['nombre' => 'María', 'apellido' => 'Hernández Soto', 'edad' => 42, 'meses_empresa' => 60, 'genero' => 'Femenino'],
            ['nombre' => 'Luis', 'apellido' => 'Pérez Díaz', 'edad' => 24, 'meses_empresa' => 3, 'genero' => 'Masculino'],
            ['nombre' => 'Laura', 'apellido' => 'Sánchez Vega', 'edad' => 31, 'meses_empresa' => 18, 'genero' => 'Femenino'],
            ['nombre' => 'Roberto', 'apellido' => 'Ramírez Flores', 'edad' => 50, 'meses_empresa' => 96, 'genero' => 'Masculino'],
            ['nombre' => 'Patricia', 'apellido' => 'Torres Mendoza', 'edad' => 26, 'meses_empresa' => 6, 'genero' => 'Femenino'],
            ['nombre' => 'Jorge', 'apellido' => 'López Reyes', 'edad' => 39, 'meses_empresa' => 36, 'genero' => 'Masculino'],
            ['nombre' => 'Sandra', 'apellido' => 'González Cruz', 'edad' => 45, 'meses_empresa' => 72, 'genero' => 'Femenino'],
            ['nombre' => 'Miguel', 'apellido' => 'Díaz Ortiz', 'edad' => 22, 'meses_empresa' => 1, 'genero' => 'Masculino'],
        ];

        foreach ($datos as $i => $d) {
            $apellidos = explode(' ', $d['apellido'], 2);
            $fechaNac = Carbon::today()->subYears($d['edad'])->format('Y-m-d');
            $fechaIngreso = Carbon::today()->subMonths($d['meses_empresa'])->format('Y-m-d');

            $user = $colaboradorService->crearColaborador([
                'name' => $d['nombre'],
                'apellido_paterno' => $apellidos[0] ?? 'N/A',
                'apellido_materno' => $apellidos[1] ?? '',
                'email' => 'colab.segmentacion.'.($i + 1).'@test.local',
                'password' => Str::password(32),
                'numero_colaborador' => 'SEG'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => $fechaNac,
                'fecha_ingreso' => $fechaIngreso,
                'genero' => $d['genero'],
                'periodicidad_pago' => 'QUINCENAL',
                'ubicacion_id' => $uIds[$i % count($uIds)] ?? null,
                'departamento_id' => $dIds[$i % count($dIds)] ?? null,
                'area_id' => $aIds[$i % count($aIds)] ?? null,
                'puesto_id' => $pIds[$i % count($pIds)] ?? null,
                'region_id' => $rIds[$i % count($rIds)] ?? null,
            ], $empresa);
            $user->empresas()->syncWithoutDetaching([
                $empresa->id,
            ]);
        }
    }
}
