<?php

namespace App\Filament\Cliente\Resources\Vacantes\Pages;

use App\Filament\Cliente\Resources\Vacantes\VacanteResource;
use App\Filament\Cliente\Widgets\UxPrototype\VacantesHeroWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVacantes extends ListRecords
{
    protected static string $resource = VacanteResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            VacantesHeroWidget::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
