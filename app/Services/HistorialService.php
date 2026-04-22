<?php

namespace App\Services;

use App\Models\HistorialArea;
use App\Models\HistorialDepartamento;
use App\Models\HistorialPeriodicidadPago;
use App\Models\HistorialPuesto;
use App\Models\HistorialRazonSocial;
use App\Models\HistorialRegion;
use App\Models\HistorialUbicacion;
use App\Models\User;

class HistorialService
{
    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function withColaboradorId(User $user, array $attributes): array
    {
        if ($user->colaborador_id !== null) {
            $attributes['colaborador_id'] = $user->colaborador_id;
        }

        return $attributes;
    }

    /**
     * Crea registros de historial iniciales para cada catálogo asignado al colaborador (User).
     * fecha_inicio = fecha_ingreso, fecha_fin = null.
     */
    public function crearHistorialesIniciales(User $user): void
    {
        $user->loadMissing('colaborador');
        $ficha = $user->colaborador;

        $fechaInicio = $ficha?->fecha_ingreso ?? $user->fecha_ingreso;

        $ubicacionId = $ficha?->ubicacion_id;
        if ($ubicacionId) {
            HistorialUbicacion::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'ubicacion_id' => $ubicacionId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $departamentoId = $ficha?->departamento_id;
        if ($departamentoId) {
            HistorialDepartamento::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'departamento_id' => $departamentoId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $areaId = $ficha?->area_id;
        if ($areaId) {
            HistorialArea::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'area_id' => $areaId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $puestoId = $ficha?->puesto_id;
        if ($puestoId) {
            HistorialPuesto::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'puesto_id' => $puestoId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $regionId = $ficha?->region_id;
        if ($regionId) {
            HistorialRegion::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'region_id' => $regionId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $razonSocialId = $ficha?->razon_social_id;
        if ($razonSocialId) {
            HistorialRazonSocial::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'razon_social_id' => $razonSocialId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }

        $periodicidad = $ficha?->periodicidad_pago ?? $user->periodicidad_pago;
        if ($periodicidad) {
            HistorialPeriodicidadPago::create($this->withColaboradorId($user, [
                'user_id' => $user->id,
                'valor_periodicidad' => $periodicidad,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => null,
            ]));
        }
    }

    /**
     * @param  string  $tipoCatalogo  'ubicacion'|'departamento'|'area'|'puesto'|'region'|'razon_social'|'periodicidad_pago'
     * @param  int|string|null  $nuevoValor  ID del catálogo o valor_periodicidad para periodicidad_pago. Si es null/0, solo se cierra el activo.
     */
    public function registrarCambio(User $user, string $tipoCatalogo, int|string|null $nuevoValor): void
    {
        $hoy = now()->toDateString();
        $esPeriodicidad = $tipoCatalogo === 'periodicidad_pago';
        $valorVacio = $esPeriodicidad ? ($nuevoValor === null || $nuevoValor === '') : ($nuevoValor === null || $nuevoValor === 0);

        if ($valorVacio) {
            $this->cerrarHistorialActivo($user, $tipoCatalogo, $hoy);

            return;
        }

        match ($tipoCatalogo) {
            'ubicacion' => $this->cerrarYCrearHistorialUbicacion($user, (int) $nuevoValor, $hoy),
            'departamento' => $this->cerrarYCrearHistorialDepartamento($user, (int) $nuevoValor, $hoy),
            'area' => $this->cerrarYCrearHistorialArea($user, (int) $nuevoValor, $hoy),
            'puesto' => $this->cerrarYCrearHistorialPuesto($user, (int) $nuevoValor, $hoy),
            'region' => $this->cerrarYCrearHistorialRegion($user, (int) $nuevoValor, $hoy),
            'razon_social' => $this->cerrarYCrearHistorialRazonSocial($user, (int) $nuevoValor, $hoy),
            'periodicidad_pago' => $this->cerrarYCrearHistorialPeriodicidad($user, (string) $nuevoValor, $hoy),
            default => null,
        };
    }

    private function cerrarHistorialActivo(User $user, string $tipoCatalogo, string $hoy): void
    {
        match ($tipoCatalogo) {
            'ubicacion' => HistorialUbicacion::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'departamento' => HistorialDepartamento::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'area' => HistorialArea::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'puesto' => HistorialPuesto::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'region' => HistorialRegion::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'razon_social' => HistorialRazonSocial::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            'periodicidad_pago' => HistorialPeriodicidadPago::query()->where('user_id', $user->id)->whereNull('fecha_fin')->update(['fecha_fin' => $hoy]),
            default => null,
        };
    }

    private function cerrarYCrearHistorialUbicacion(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialUbicacion::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialUbicacion::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'ubicacion_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialDepartamento(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialDepartamento::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialDepartamento::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'departamento_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialArea(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialArea::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialArea::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'area_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialPuesto(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialPuesto::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialPuesto::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'puesto_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialRegion(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialRegion::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialRegion::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'region_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialRazonSocial(User $user, int $nuevoValor, string $hoy): void
    {
        HistorialRazonSocial::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialRazonSocial::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'razon_social_id' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }

    private function cerrarYCrearHistorialPeriodicidad(User $user, string $nuevoValor, string $hoy): void
    {
        HistorialPeriodicidadPago::query()
            ->where('user_id', $user->id)
            ->whereNull('fecha_fin')
            ->update(['fecha_fin' => $hoy]);

        HistorialPeriodicidadPago::create($this->withColaboradorId($user, [
            'user_id' => $user->id,
            'valor_periodicidad' => $nuevoValor,
            'fecha_inicio' => $hoy,
            'fecha_fin' => null,
        ]));
    }
}
