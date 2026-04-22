<?php

declare(strict_types=1);

namespace App\Services\NotificacionesPush;

use App\Contracts\ObtenerDestinatariosPushInterface;
use App\Models\Colaborador;
use App\Models\NotificacionPush;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ObtenerDestinatariosReal implements ObtenerDestinatariosPushInterface
{
    /**
     * Obtiene placeholders por colaborador hasta existir tabla de tokens / player_id.
     *
     * @return Collection<int, string>
     */
    public function obtenerTokens(NotificacionPush $notificacion): Collection
    {
        return $this->obtenerColaboradoresFiltrados($notificacion)
            ->pluck('id')
            ->map(fn (int $id): string => "colaborador-{$id}-placeholder");
    }

    public function contarDestinatarios(NotificacionPush $notificacion): int
    {
        return $this->buildQuery($notificacion)->count();
    }

    /**
     * @return Collection<int, Colaborador>
     */
    public function obtenerColaboradoresFiltrados(NotificacionPush $notificacion): Collection
    {
        return $this->buildQuery($notificacion)
            ->with(['ubicacion', 'puesto'])
            ->get();
    }

    public function obtenerColaboradoresPaginados(
        NotificacionPush $notificacion,
        int $perPage = 50,
        ?string $busqueda = null
    ): LengthAwarePaginator {
        $query = $this->buildQuery($notificacion);

        $busqueda = $busqueda !== null ? trim($busqueda) : '';
        if ($busqueda !== '') {
            $query->where(function (Builder $q) use ($busqueda): void {
                $like = '%'.$busqueda.'%';
                $q->where('nombre', 'like', $like)
                    ->orWhere('apellido_paterno', 'like', $like)
                    ->orWhere('apellido_materno', 'like', $like)
                    ->orWhere('numero_colaborador', 'like', $like);
            });
        }

        return $query->with(['ubicacion', 'puesto'])->paginate($perPage);
    }

    /**
     * @return Builder<Colaborador>
     */
    protected function buildQuery(NotificacionPush $notificacion): Builder
    {
        $filtros = $notificacion->getFiltrosConDefaults();
        unset($filtros['destinatarios']);
        $empresaId = $notificacion->empresa_id;

        Log::debug('ObtenerDestinatariosReal: Construyendo query', [
            'notificacion_id' => $notificacion->id,
            'empresa_id' => $empresaId,
            'filtros' => $filtros,
        ]);

        $query = Colaborador::query()
            ->where('empresa_id', $empresaId)
            ->whereNull('deleted_at');

        $query->whereHas('user', function (Builder $q): void {
            $q->whereNull('deleted_at');
        });

        $this->aplicarFiltroUbicaciones($query, $filtros);
        $this->aplicarFiltroDepartamentos($query, $filtros);
        $this->aplicarFiltroAreas($query, $filtros);
        $this->aplicarFiltroPuestos($query, $filtros);
        $this->aplicarFiltroGeneros($query, $filtros);
        $this->aplicarFiltroEdad($query, $filtros);
        $this->aplicarFiltroAntiguedad($query, $filtros);
        $this->aplicarFiltroCumpleaneros($query, $filtros);
        $this->aplicarFiltroAdeudos($query, $filtros);

        return $query->orderBy('apellido_paterno')->orderBy('nombre');
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroUbicaciones(Builder $query, array $filtros): void
    {
        if (! empty($filtros['ubicaciones'])) {
            $query->whereIn('ubicacion_id', $filtros['ubicaciones']);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroDepartamentos(Builder $query, array $filtros): void
    {
        if (! empty($filtros['departamentos'])) {
            $query->whereIn('departamento_id', $filtros['departamentos']);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroAreas(Builder $query, array $filtros): void
    {
        if (! empty($filtros['areas'])) {
            $query->whereIn('area_id', $filtros['areas']);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroPuestos(Builder $query, array $filtros): void
    {
        if (! empty($filtros['puestos'])) {
            $query->whereIn('puesto_id', $filtros['puestos']);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroGeneros(Builder $query, array $filtros): void
    {
        if (! empty($filtros['generos'])) {
            $query->whereIn('genero', $filtros['generos']);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroEdad(Builder $query, array $filtros): void
    {
        $edadMinima = $filtros['edad_minima'] ?? null;
        $edadMaxima = $filtros['edad_maxima'] ?? null;

        if ($edadMinima !== null && (int) $edadMinima > 0) {
            $fechaMaxNacimiento = now()->subYears((int) $edadMinima)->endOfDay();
            $query->where('fecha_nacimiento', '<=', $fechaMaxNacimiento);
        }

        if ($edadMaxima !== null && (int) $edadMaxima > 0) {
            $fechaMinNacimiento = now()->subYears((int) $edadMaxima + 1)->startOfDay();
            $query->where('fecha_nacimiento', '>', $fechaMinNacimiento);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroAntiguedad(Builder $query, array $filtros): void
    {
        $antiguedadMinima = $filtros['antiguedad_minima_meses'] ?? null;
        $antiguedadMaxima = $filtros['antiguedad_maxima_meses'] ?? null;

        if ($antiguedadMinima !== null && (int) $antiguedadMinima > 0) {
            $fechaMaxIngreso = now()->subMonths((int) $antiguedadMinima)->endOfDay();
            $query->where('fecha_ingreso', '<=', $fechaMaxIngreso);
        }

        if ($antiguedadMaxima !== null && (int) $antiguedadMaxima > 0) {
            $fechaMinIngreso = now()->subMonths((int) $antiguedadMaxima)->startOfDay();
            $query->where('fecha_ingreso', '>=', $fechaMinIngreso);
        }
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroCumpleaneros(Builder $query, array $filtros): void
    {
        $raw = $filtros['cumpleaneros_mes'] ?? null;
        if ($raw === null || $raw === '' || $raw === []) {
            return;
        }

        $meses = is_array($raw) ? $raw : [$raw];
        $meses = array_values(array_filter(array_map(static fn (mixed $m): int => (int) $m, $meses), static fn (int $m): bool => $m >= 1 && $m <= 12));

        if ($meses === []) {
            return;
        }

        $query->where(function (Builder $q) use ($meses): void {
            foreach ($meses as $mes) {
                $q->orWhereMonth('fecha_nacimiento', $mes);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    protected function aplicarFiltroAdeudos(Builder $query, array $filtros): void
    {
        $conAdeudos = $filtros['con_adeudos'] ?? null;

        if ($conAdeudos === null) {
            return;
        }

        Log::debug('ObtenerDestinatariosReal: Filtro con_adeudos solicitado pero no implementado', [
            'con_adeudos' => $conAdeudos,
        ]);
    }
}
