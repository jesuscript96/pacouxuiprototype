<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::satisfactionENPS.
 */
final class SatisfaccionEnpsTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'satisfaccion_enps';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/satisfaccion-enps';
    }
}
