<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class DescuentosTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'descuentos';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/descuentos';
    }
}
