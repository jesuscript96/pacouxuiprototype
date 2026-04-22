<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class EncuestasPlanAccionTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'encuestas_plan_accion';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/encuestas-plan-accion';
    }
}
