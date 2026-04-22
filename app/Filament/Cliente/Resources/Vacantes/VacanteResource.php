<?php

namespace App\Filament\Cliente\Resources\Vacantes;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Vacantes\Pages\CreateVacante;
use App\Filament\Cliente\Resources\Vacantes\Pages\EditVacante;
use App\Filament\Cliente\Resources\Vacantes\Pages\ListVacantes;
use App\Filament\Cliente\Resources\Vacantes\Pages\ViewVacante;
use App\Filament\Cliente\Resources\Vacantes\RelationManagers\CandidatosRelationManager;
use App\Filament\Cliente\Resources\Vacantes\Schemas\VacanteForm;
use App\Filament\Cliente\Resources\Vacantes\Schemas\VacanteInfolist;
use App\Filament\Cliente\Resources\Vacantes\Tables\VacantesTable;
use App\Models\Vacante;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class VacanteResource extends Resource
{
    protected static ?string $model = Vacante::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $navigationLabel = 'Vacantes';

    protected static ?string $modelLabel = 'Vacante';

    protected static ?string $pluralModelLabel = 'Vacantes';

    protected static bool $isScopedToTenant = true;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::RECLUTAMIENTO;

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return VacanteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VacantesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return VacanteInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            CandidatosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacantes::route('/'),
            'create' => CreateVacante::route('/create'),
            'view' => ViewVacante::route('/{record}'),
            'edit' => EditVacante::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['creador'])
            ->withCount('candidatos');
    }

    // === Permisos (panel Cliente — sin ShieldPlugin) ===

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Vacante');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:Vacante');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:Vacante');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:Vacante');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:Vacante');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:Vacante');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:Vacante');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:Vacante');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:Vacante');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:Vacante');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:Vacante');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:Vacante');
    }
}
