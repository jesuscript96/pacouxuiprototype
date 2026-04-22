<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Empresa;
use App\Models\FiltroColaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ColaboradoresFiltroAdminService
{
    /**
     * @param  array<int|string>|int|string|null  $value
     * @return array<int|string|null>
     */
    public static function normalizarIdsDeFiltro(array|int|string|null $value): array
    {
        $arr = is_array($value) ? array_values($value) : (($value !== null && $value !== '') ? [$value] : []);

        return $arr === [] ? [null] : $arr;
    }

    /**
     * Primer ID de un multiselect para persistir en FK nullable (sin columna JSON de criterios).
     *
     * @param  array<int|string>|mixed  $value
     */
    public static function primerId(mixed $value): ?int
    {
        $ids = array_values(array_filter((array) ($value ?? []), static fn ($id): bool => $id !== null && $id !== ''));

        return isset($ids[0]) ? (int) $ids[0] : null;
    }

    /**
     * Aplica criterios sobre la ficha `colaboradores` vinculada al user (User → colaborador → catálogos).
     *
     * @param  Builder<User>  $query
     * @param  array<string, mixed>  $data
     * @return Builder<User>
     */
    public static function aplicarFiltrosFormulario(Builder $query, array $data): Builder
    {
        $regionIds = array_filter((array) (self::normalizarIdsDeFiltro($data['region_id'] ?? [])));
        $ubicacionIds = array_filter((array) (self::normalizarIdsDeFiltro($data['ubicacion_id'] ?? [])));
        $departamentoIds = array_filter((array) (self::normalizarIdsDeFiltro($data['departamento_id'] ?? [])));
        $areaIds = array_filter((array) (self::normalizarIdsDeFiltro($data['area_id'] ?? [])));
        $puestoIds = array_filter((array) (self::normalizarIdsDeFiltro($data['puesto_id'] ?? [])));
        $generos = is_array($data['generos'] ?? null) ? $data['generos'] : [];
        $meses = is_array($data['meses'] ?? null) ? $data['meses'] : [];
        $edadDesde = (int) ($data['edad_desde'] ?? 0);
        $edadHasta = (int) ($data['edad_hasta'] ?? 0);
        $mesDesde = (int) ($data['mes_desde'] ?? 0);
        $mesHasta = (int) ($data['mes_hasta'] ?? 0);

        $aplicaEdad = $edadHasta > 0;
        $aplicaAntiguedad = $mesHasta > 0;

        if (
            $regionIds === [] && $ubicacionIds === [] && $departamentoIds === [] && $areaIds === [] && $puestoIds === []
            && $generos === [] && $meses === [] && ! $aplicaEdad && ! $aplicaAntiguedad
        ) {
            return $query;
        }

        $query->whereHas('colaborador', function (Builder $q) use (
            $regionIds,
            $ubicacionIds,
            $departamentoIds,
            $areaIds,
            $puestoIds,
            $generos,
            $meses,
            $edadDesde,
            $edadHasta,
            $aplicaEdad,
            $mesDesde,
            $mesHasta,
            $aplicaAntiguedad,
        ): void {
            if ($regionIds !== []) {
                $q->whereIn('region_id', $regionIds);
            }
            if ($ubicacionIds !== []) {
                $q->whereIn('ubicacion_id', $ubicacionIds);
            }
            if ($departamentoIds !== []) {
                $q->whereIn('departamento_id', $departamentoIds);
            }
            if ($areaIds !== []) {
                $q->whereIn('area_id', $areaIds);
            }
            if ($puestoIds !== []) {
                $q->whereIn('puesto_id', $puestoIds);
            }
            if ($generos !== []) {
                $q->whereIn('genero', $generos);
            }
            if ($meses !== []) {
                $q->whereIn(DB::raw('DATE_FORMAT(fecha_nacimiento, "%m")'), $meses);
            }
            if ($aplicaEdad) {
                $q->whereRaw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) >= ?', [$edadDesde])
                    ->whereRaw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) <= ?', [$edadHasta]);
            }
            if ($aplicaAntiguedad) {
                $q->whereRaw('TIMESTAMPDIFF(MONTH, fecha_ingreso, CURDATE()) >= ?', [$mesDesde])
                    ->whereRaw('TIMESTAMPDIFF(MONTH, fecha_ingreso, CURDATE()) <= ?', [$mesHasta]);
            }
        });

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formularioDesdeFiltro(?FiltroColaborador $filtro): array
    {
        $base = [
            'region_id' => [],
            'ubicacion_id' => [],
            'departamento_id' => [],
            'area_id' => [],
            'puesto_id' => [],
            'generos' => [],
            'meses' => [],
            'edad_desde' => null,
            'edad_hasta' => null,
            'mes_desde' => null,
            'mes_hasta' => null,
        ];

        if ($filtro === null) {
            return $base;
        }

        return [
            'region_id' => $filtro->region_id ? [$filtro->region_id] : [],
            'ubicacion_id' => $filtro->ubicacion_id ? [$filtro->ubicacion_id] : [],
            'departamento_id' => $filtro->departamento_id ? [$filtro->departamento_id] : [],
            'area_id' => $filtro->area_id ? [$filtro->area_id] : [],
            'puesto_id' => $filtro->puesto_id ? [$filtro->puesto_id] : [],
            'generos' => is_array($filtro->generos) ? $filtro->generos : [],
            'meses' => is_array($filtro->meses) ? $filtro->meses : [],
            'edad_desde' => $filtro->edad_desde,
            'edad_hasta' => $filtro->edad_hasta,
            'mes_desde' => $filtro->mes_desde,
            'mes_hasta' => $filtro->mes_hasta,
        ];
    }

    /**
     * Reemplaza los filtros guardados del usuario en esa empresa (misma idea que el legado al crear un filtro nuevo).
     * BL: solo el primer ID de cada multiselect se guarda en FKs; meses (mes de nacimiento) y generos van como arrays en JSON.
     */
    public static function persistir(string $nombre, Empresa $empresa, User $usuarioDestino, array $data): FiltroColaborador
    {
        FiltroColaborador::query()
            ->where('empresa_id', $empresa->id)
            ->where('user_id', $usuarioDestino->id)
            ->delete();

        $generos = array_values(array_filter((array) ($data['generos'] ?? [])));
        $meses = array_values(array_filter((array) ($data['meses'] ?? [])));

        return FiltroColaborador::create([
            'nombre' => $nombre,
            'empresa_id' => $empresa->id,
            'user_id' => $usuarioDestino->id,
            'region_id' => self::primerId($data['region_id'] ?? []),
            'ubicacion_id' => self::primerId($data['ubicacion_id'] ?? []),
            'departamento_id' => self::primerId($data['departamento_id'] ?? []),
            'area_id' => self::primerId($data['area_id'] ?? []),
            'puesto_id' => self::primerId($data['puesto_id'] ?? []),
            'meses' => $meses,
            'generos' => $generos,
            'edad_desde' => $data['edad_desde'] !== null && $data['edad_desde'] !== '' ? (int) $data['edad_desde'] : null,
            'edad_hasta' => $data['edad_hasta'] !== null && $data['edad_hasta'] !== '' ? (int) $data['edad_hasta'] : null,
            'mes_desde' => $data['mes_desde'] !== null && $data['mes_desde'] !== '' ? (int) $data['mes_desde'] : null,
            'mes_hasta' => $data['mes_hasta'] !== null && $data['mes_hasta'] !== '' ? (int) $data['mes_hasta'] : null,
        ]);
    }
}
