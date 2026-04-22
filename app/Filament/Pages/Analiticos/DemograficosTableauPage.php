<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::demographics — panel Admin sin tenant (usuario embed global).
 */
final class DemograficosTableauPage extends AdminTableauReportPage
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
