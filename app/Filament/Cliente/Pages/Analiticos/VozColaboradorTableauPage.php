<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class VozColaboradorTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'voz_colaborador';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/voz-colaborador';
    }
}
