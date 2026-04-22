<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\VerDestinatariosDocumentos\Pages;

use App\Filament\Cliente\Resources\VerDestinatariosDocumentos\VerDestinatariosDocumentosResource;
use App\Models\Empresa;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ListVerDestinatariosDocumentos extends ListRecords
{
    protected static string $resource = VerDestinatariosDocumentosResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Destinatarios de Documentos Corporativos';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Verifica qué documentos fueron enviados y qué usuario los ha visto.';
    }

    // Ancho de pantalla
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::MaxContent;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_excel')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->visible(fn (): bool => (bool) auth()->user()?->can('ViewAny:DocumentoCorporativo'))
                ->action(function (): void {
                    $inquilino = Filament::getTenant();
                    if (! $inquilino instanceof Empresa) {
                        return;
                    }

                    $clave = Str::uuid()->toString();
                    Cache::put(
                        'export_doc_dest_'.$clave,
                        [
                            'user_id' => auth()->id(),
                            'empresa_id' => $inquilino->id,
                            'filters' => $this->tableFilters ?? [],
                            'search' => $this->getTableSearch(),
                        ],
                        now()->addMinutes(10),
                    );

                    $destino = URL::temporarySignedRoute(
                        'cliente.documentos-corporativos.destinatarios.exportar',
                        now()->addMinutes(10),
                        ['k' => $clave],
                    );

                    $this->redirect($destino);
                }),
        ];
    }
}
