<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::satisfactionENPS — panel Admin sin tenant.
 */
final class SatisfaccionEnpsTableauPage extends AdminTableauReportPage
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
