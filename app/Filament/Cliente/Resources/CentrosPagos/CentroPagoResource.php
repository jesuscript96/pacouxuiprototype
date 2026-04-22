<?php

namespace App\Filament\Cliente\Resources\CentrosPagos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\CentrosPagos\Pages\CreateCentroPago;
use App\Filament\Cliente\Resources\CentrosPagos\Pages\EditCentroPago;
use App\Filament\Cliente\Resources\CentrosPagos\Pages\ListCentrosPagos;
use App\Filament\Cliente\Resources\CentrosPagos\Pages\ViewCentroPago;
use App\Filament\Cliente\Resources\CentrosPagos\Schemas\CentroPagoForm;
use App\Filament\Cliente\Resources\CentrosPagos\Tables\CentrosPagosTable;
use App\Models\CentroPago;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CentroPagoResource extends Resource
{
    protected static ?string $model = CentroPago::class;

    protected static ?string $navigationLabel = 'Centros de pago';

    protected static ?string $modelLabel = 'Centro de pago';

    protected static ?string $pluralModelLabel = 'Centros de pago';

    protected static bool $isScopedToTenant = true;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::CATALOGOS_COLABORADORES;

    public static function getNavigationUrl(): string
    {
        return \App\Filament\Cliente\Pages\Catalogos\CatalogosPage::getUrl().'?tab=centros_pago';
    }

    public static function form(Schema $schema): Schema
    {
        return CentroPagoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentrosPagosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCentrosPagos::route('/'),
            'create' => CreateCentroPago::route('/create'),
            'view' => ViewCentroPago::route('/{record}'),
            'edit' => EditCentroPago::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:CentroPago');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:CentroPago');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:CentroPago');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:CentroPago');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:CentroPago');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:CentroPago');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:CentroPago');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:CentroPago');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:CentroPago');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:CentroPago');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:CentroPago');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:CentroPago');
    }
}
