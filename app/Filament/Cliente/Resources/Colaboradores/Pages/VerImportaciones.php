<?php

namespace App\Filament\Cliente\Resources\Colaboradores\Pages;

use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Models\Empresa;
use App\Models\Importacion;
use Filament\Facades\Filament;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class VerImportaciones extends Page
{
    protected static string $resource = ColaboradorResource::class;

    protected string $view = 'filament.cliente.resources.colaboradores.pages.ver-importaciones';

    public function getTitle(): string|Htmlable
    {
        return 'Estado de importaciones';
    }

    /**
     * @return Collection<int, Importacion>
     */
    public function getImportaciones(): Collection
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            return collect();
        }

        return Importacion::query()
            ->where('empresa_id', $tenant->id)
            ->with('usuario:id,name,apellido_paterno,apellido_materno')
            ->latest('id')
            ->limit(50)
            ->get();
    }

    public function getDescargaErroresUrl(Importacion $importacion): ?string
    {
        if (! $importacion->archivo_errores) {
            return null;
        }

        return route('cliente.importaciones.descargar-errores', [
            'importacion' => $importacion->id,
        ]);
    }
}
