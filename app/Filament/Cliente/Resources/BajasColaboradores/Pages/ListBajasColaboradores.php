<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores\Pages;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListBajasColaboradores extends ListRecords
{
    protected static string $resource = BajaColaboradorResource::class;

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('bajaMasiva')
                ->label('Baja masiva')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->url(fn (): string => BajaColaboradorResource::getUrl('importar', [
                    'tenant' => Filament::getTenant(),
                ]))
                ->visible(fn (): bool => (bool) auth()->user()?->can('Create:BajaColaborador')),
        ];
    }
}
