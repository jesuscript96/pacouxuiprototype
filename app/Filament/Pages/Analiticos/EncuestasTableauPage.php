<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos;

use Filament\Panel;

/**
 * BL legacy: TableauServerController::surveys — panel Admin sin tenant.
 */
final class EncuestasTableauPage extends AdminTableauReportPage
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
