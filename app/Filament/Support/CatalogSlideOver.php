<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;

/**
 * BL: Acciones de tabla con panel lateral (slide-over) para catálogos del panel Admin.
 */
final class CatalogSlideOver
{
    public static function defaultWidth(): Width
    {
        return Width::TwoExtraLarge;
    }

    public static function editAction(?Width $width = null): EditAction
    {
        return EditAction::make()
            ->slideOver()
            ->modalWidth($width ?? self::defaultWidth());
    }

    public static function viewAction(?Width $width = null): ViewAction
    {
        return ViewAction::make()
            ->slideOver()
            ->modalWidth($width ?? self::defaultWidth());
    }
}
