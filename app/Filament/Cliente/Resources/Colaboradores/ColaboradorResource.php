<?php

namespace App\Filament\Cliente\Resources\Colaboradores;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Colaboradores\Pages\CreateColaborador;
use App\Filament\Cliente\Resources\Colaboradores\Pages\EditColaborador;
use App\Filament\Cliente\Resources\Colaboradores\Pages\ListColaboradores;
use App\Filament\Cliente\Resources\Colaboradores\Pages\VerImportaciones;
use App\Filament\Cliente\Resources\Colaboradores\Pages\ViewColaborador;
use App\Filament\Cliente\Resources\Colaboradores\Schemas\ColaboradorForm;
use App\Filament\Cliente\Resources\Colaboradores\Tables\ColaboradoresTable;
use App\Models\Colaborador;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ColaboradorResource extends Resource
{
    protected static ?string $model = Colaborador::class;

    protected static ?string $navigationLabel = 'Colaboradores';

    // BL: el scope por tenant vía BelongsTo(empresa) excluye colaboradores solo en pivot; el filtro va en getEloquentQuery().
    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::GESTION_PERSONAL;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nombre_completo';

    public static function getModelLabel(): string
    {
        return 'Colaborador';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Colaboradores';
    }

    public static function form(Schema $schema): Schema
    {
        return ColaboradorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColaboradoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColaboradores::route('/'),
            'create' => CreateColaborador::route('/create'),
            // Rutas literales antes de /{record}: si no, "importaciones" se resuelve como colaborador y responde 404.
            'importaciones' => VerImportaciones::route('/importaciones'),
            'view' => ViewColaborador::route('/{record}'),
            'edit' => EditColaborador::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);

        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            $query->where('empresa_id', $tenant->id);
        }

        return $query->with([
            'empresa',
            'ubicacion',
            'departamento',
            'area',
            'puesto',
            'region',
            'centroPago',
            'user',
            'bajaProgramada',
            'ultimaBaja',
        ]);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre', 'apellido_paterno', 'apellido_materno', 'numero_colaborador', 'email'];
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Colaborador');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:Colaborador');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:Colaborador');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:Colaborador');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:Colaborador');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:Colaborador');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:Colaborador');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:Colaborador');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:Colaborador');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:Colaborador');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:Colaborador');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:Colaborador');
    }
}
