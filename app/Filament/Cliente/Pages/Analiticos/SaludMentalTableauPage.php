<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class SaludMentalTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'salud_mental';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/salud-mental';
    }
}
