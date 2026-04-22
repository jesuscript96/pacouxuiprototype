<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Closure;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;

/**
 * BL: Listados de catálogo Admin con crear/editar/ver en panel lateral (estilo Supabase)
 * en lugar de navegar a páginas completas. Las rutas create/edit/view siguen registradas
 * para enlaces directos, pruebas y permisos.
 */
abstract class CatalogAdminListRecords extends ListRecords
{
    protected static Width|string|null $catalogSlideOverWidth = Width::TwoExtraLarge;

    public function getDefaultActionUrl(Action $action): ?string
    {
        if ($action instanceof CreateAction || $action instanceof EditAction || $action instanceof ViewAction) {
            return null;
        }

        return parent::getDefaultActionUrl($action);
    }

    public function getDefaultActionSchemaResolver(Action $action): ?Closure
    {
        return match (true) {
            $action instanceof CreateAction, $action instanceof EditAction => fn (Schema $schema): Schema => $this->form(
                $schema->hasCustomColumns() ? $schema : $schema->columns(1)
            ),
            $action instanceof ViewAction => fn (Schema $schema): Schema => $this->infolist(
                $this->form($schema->hasCustomColumns() ? $schema : $schema->columns(1))
            ),
            default => parent::getDefaultActionSchemaResolver($action),
        };
    }

    protected function makeTable(): Table
    {
        $table = parent::makeTable();
        $table->recordUrl(null);

        if ($table->hasAction('edit')) {
            $table->recordAction('edit');
        } elseif ($table->hasAction('view')) {
            $table->recordAction('view');
        }

        return $table;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->makeCatalogCreateAction(),
        ];
    }

    protected function makeCatalogCreateAction(): CreateAction
    {
        $action = CreateAction::make()
            ->slideOver()
            ->modalWidth(static::$catalogSlideOverWidth);

        return $this->configureCatalogCreateAction($action);
    }

    protected function configureCatalogCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }
}
