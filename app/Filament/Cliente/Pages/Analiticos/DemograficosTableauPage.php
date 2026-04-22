<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::demographics — mismo flujo JWT/usuario que rotación.
 */
final class DemograficosTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'demograficos';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/demograficos';
    }
}
