<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Jefe;
use App\Models\Puesto;
use App\Models\Ubicacion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Puebla `jefes` (legacy `bosses`) con códigos por nivel alineados a {@see Colaborador::$codigo_boss}.
 *
 * Requisitos: catálogos por empresa (p. ej. {@see EmpresaCatalogosDemostrativosSeeder}) y al menos un `colaborador`
 * por empresa. Si el primer colaborador de cada empresa (id 1 y 2) no tiene FK de ubicación/depto/área/puesto,
 * se completan con el primer registro de cada catálogo de esa empresa para poder generar filas demo.
 */
class JefesSeeder extends Seeder
{
    use WithoutModelEvents;

    private const int MAX_JEFES_POR_EMPRESA = 6;

    public function run(): void
    {
        $empresas = Empresa::query()->orderBy('id')->limit(2)->get();
        if ($empresas->isEmpty()) {
            $this->command?->warn('JefesSeeder: no hay empresas; se omite.');

            return;
        }

        foreach ($empresas as $empresa) {
            $this->asegurarPrimerColaboradorConCatalogoRh((int) $empresa->getKey());
        }

        $creados = 0;
        foreach ($empresas as $empresa) {
            $creados += $this->sembrarJefesParaEmpresa((int) $empresa->getKey());
        }

        if ($creados === 0) {
            if (Jefe::query()->exists()) {
                $this->command?->info('JefesSeeder: sin filas nuevas (registros previos en `jefes`).');
            } else {
                $this->command?->info('JefesSeeder: sin colaboradores con catálogo RH completo; no se insertaron filas. Opcional: ejecutar ColaboradoresYUsuariosDemostrativosSeeder.');
            }
        } else {
            $this->command?->info("JefesSeeder: {$creados} registro(s) nuevo(s) en `jefes`.");
        }
    }

    private function asegurarPrimerColaboradorConCatalogoRh(int $empresaId): void
    {
        $ubicacion = Ubicacion::query()->where('empresa_id', $empresaId)->orderBy('id')->first();
        $departamento = Departamento::query()->where('empresa_id', $empresaId)->orderBy('id')->first();
        $area = Area::query()->where('empresa_id', $empresaId)->orderBy('id')->first();
        $puesto = Puesto::query()->where('empresa_id', $empresaId)->orderBy('id')->first();

        if ($ubicacion === null || $departamento === null || $area === null || $puesto === null) {
            return;
        }

        $colaborador = Colaborador::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('id')
            ->first();

        if ($colaborador === null) {
            return;
        }

        if (
            $colaborador->ubicacion_id !== null
            && $colaborador->departamento_id !== null
            && $colaborador->area_id !== null
            && $colaborador->puesto_id !== null
        ) {
            return;
        }

        $colaborador->update([
            'ubicacion_id' => $ubicacion->id,
            'departamento_id' => $departamento->id,
            'area_id' => $area->id,
            'puesto_id' => $puesto->id,
        ]);
    }

    private function sembrarJefesParaEmpresa(int $empresaId): int
    {
        $creados = 0;

        $colaboradores = Colaborador::query()
            ->where('empresa_id', $empresaId)
            ->whereNotNull('ubicacion_id')
            ->whereNotNull('departamento_id')
            ->whereNotNull('area_id')
            ->whereNotNull('puesto_id')
            ->orderBy('id')
            ->limit(self::MAX_JEFES_POR_EMPRESA)
            ->get();

        foreach ($colaboradores as $colaborador) {
            $base = $colaborador->codigo_boss;
            if ($base === '') {
                continue;
            }

            $jefe = Jefe::query()->firstOrCreate(
                ['colaborador_id' => $colaborador->id],
                [
                    'codigo_nivel_1' => $base,
                    'codigo_nivel_2' => $base.'-2',
                    'codigo_nivel_3' => $base.'-3',
                    'codigo_nivel_4' => $base.'-4',
                ]
            );

            if ($jefe->wasRecentlyCreated) {
                $creados++;
            }
        }

        return $creados;
    }
}
