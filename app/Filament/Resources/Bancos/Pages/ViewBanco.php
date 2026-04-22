<?php

declare(strict_types=1);

namespace App\Filament\Resources\Bancos\Pages;

use App\Filament\Resources\Bancos\BancoResource;
use App\Models\Banco;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewBanco extends ViewRecord
{
    protected static string $resource = BancoResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Banco $record */
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
