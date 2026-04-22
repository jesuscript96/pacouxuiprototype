<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesIncluidas\Pages;

use App\Filament\Resources\NotificacionesIncluidas\NotificacionesIncluidasResource;
use App\Models\NotificacionesIncluidas;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewNotificacionesIncluidas extends ViewRecord
{
    protected static string $resource = NotificacionesIncluidasResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var NotificacionesIncluidas $record */
        $record = $this->record;

        return $record->nombre;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
