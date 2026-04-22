<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos;

use Filament\Panel;

final class RotacionPersonalTableauPage extends AdminTableauReportPage
{
    public static function reportKey(): string
    {
        return 'rotacion_personal';
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos/rotacion-personal';
    }
}
