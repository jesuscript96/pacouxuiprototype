<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Carpeta;
use App\Models\DocumentoCorporativo;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Builder;

/**
 * BL: Consulta compartida entre el listado Filament y la exportación Excel para evitar divergencia de filtros/búsqueda.
 *
 * `consultaBase` define el límite de tenant (empresa activa): no hay `empresa_id` en `documentos_corporativos`;
 * se cruza carpeta (empresa + tipo documentos corporativos) y user (misma empresa).
 */
final class DocumentosCorporativosDestinatariosConsultaService
{
    public static function consultaBase(Empresa $empresa): Builder
    {
        return DocumentoCorporativo::query()
            ->whereHas('carpeta', function (Builder $consultaCarpeta) use ($empresa): void {
                $consultaCarpeta
                    ->where('empresa_id', $empresa->id)
                    ->where('tipo', Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS);
            })
            ->whereHas('user', function (Builder $consultaUser) use ($empresa): void {
                $consultaUser->where('empresa_id', $empresa->id);
            });
    }

    /**
     * @param  array<string, mixed>  $filtrosTabla
     */
    public static function aplicarFiltros(Builder $consulta, array $filtrosTabla): Builder
    {
        $idsCarpetas = data_get($filtrosTabla, 'carpeta_id.values');
        if (filled($idsCarpetas) && is_array($idsCarpetas)) {
            $consulta->whereIn('documentos_corporativos.carpeta_id', $idsCarpetas);
        }

        $nombresDocumento = data_get($filtrosTabla, 'nombre_documento.values');
        if (filled($nombresDocumento) && is_array($nombresDocumento)) {
            $consulta->whereIn('documentos_corporativos.nombre_documento', $nombresDocumento);
        }

        return $consulta;
    }

    public static function aplicarBusqueda(Builder $consulta, ?string $busqueda): Builder
    {
        if (blank($busqueda)) {
            return $consulta;
        }

        $normalizada = mb_strtolower(trim($busqueda));
        if (str_contains($normalizada, 'no visualizado')) {
            return $consulta->where(function (Builder $interna): void {
                $interna
                    ->whereNull('documentos_corporativos.primera_visualizacion')
                    ->whereNull('documentos_corporativos.ultima_visualizacion');
            });
        }

        $termino = str_replace(['%', '_'], ['\%', '\_'], $busqueda);
        $como = "%{$termino}%";

        return $consulta->where(function (Builder $interna) use ($como): void {
            $interna
                ->where('documentos_corporativos.nombre_documento', 'like', $como)
                ->orWhere('documentos_corporativos.subcarpeta', 'like', $como)
                ->orWhereHas('carpeta', function (Builder $carpeta) use ($como): void {
                    $carpeta->where('nombre', 'like', $como);
                })
                ->orWhereHas('user', function (Builder $user) use ($como): void {
                    $user->where('name', 'like', $como)
                        ->orWhereHas('colaborador', function (Builder $colaborador) use ($como): void {
                            $colaborador->where(function (Builder $c) use ($como): void {
                                $c->where('nombre', 'like', $como)
                                    ->orWhere('apellido_paterno', 'like', $como)
                                    ->orWhere('apellido_materno', 'like', $como)
                                    ->orWhere('numero_colaborador', 'like', $como);
                            });
                        });
                });
        });
    }

    /**
     * @param  array<string, mixed>  $filtrosTabla
     */
    public static function consultaFiltrada(Empresa $empresa, array $filtrosTabla, ?string $busqueda): Builder
    {
        $consulta = self::consultaBase($empresa);
        self::aplicarFiltros($consulta, $filtrosTabla);
        self::aplicarBusqueda($consulta, $busqueda);

        return $consulta;
    }
}
