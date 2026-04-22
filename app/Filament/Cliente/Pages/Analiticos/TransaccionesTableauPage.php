<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use Filament\Panel;

final class TransaccionesTableauPage extends TableauReportPage
{
    public static function reportKey(): string
    {
        return 'transacciones';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/transacciones';
    }
}
