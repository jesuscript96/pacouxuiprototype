<?php

declare(strict_types=1);

namespace App\Filament\Pages\Analiticos\Concerns;

use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * Navegación y título compartidos entre panel Admin y Cliente para informes Tableau.
 */
trait ConfiguresTableauReportNavigation
{
    abstract public static function reportKey(): string;

    public static function getNavigationLabel(): string
    {
        return (string) config('tableau_reports.'.static::reportKey().'.label', 'Informe');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Analíticos';
    }

    public static function getNavigationSort(): ?int
    {
        $sort = config('tableau_reports.'.static::reportKey().'.navigation_sort');

        return is_numeric($sort) ? (int) $sort : null;
    }

    public function getTitle(): string|Htmlable
    {
        return (string) config('tableau_reports.'.static::reportKey().'.label', parent::getTitle());
    }
}
