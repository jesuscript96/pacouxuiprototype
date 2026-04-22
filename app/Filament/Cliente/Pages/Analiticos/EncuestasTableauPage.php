<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::surveys — vista tableau_surveys.
 */
final class EncuestasTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'encuestas';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/encuestas';
    }
}
