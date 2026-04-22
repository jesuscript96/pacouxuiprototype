<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class ResultadosNom035TableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'resultados_nom_035';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/resultados-nom-035';
    }
}
