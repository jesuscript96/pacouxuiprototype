<?php

namespace App\Filament\Cliente\Resources\BajasColaboradores;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\BajasColaboradores\Pages\EditBajaColaborador;
use App\Filament\Cliente\Resources\BajasColaboradores\Pages\ImportarBajas;
use App\Filament\Cliente\Resources\BajasColaboradores\Pages\ListBajasColaboradores;
use App\Filament\Cliente\Resources\BajasColaboradores\Pages\ReingresarColaborador;
use App\Filament\Cliente\Resources\BajasColaboradores\Pages\ViewBajaColaborador;
use App\Filament\Cliente\Resources\BajasColaboradores\Schemas\BajaColaboradorForm;
use App\Filament\Cliente\Resources\BajasColaboradores\Tables\BajasColaboradoresTable;
use App\Models\BajaColaborador;
use App\Models\Empresa;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class BajaColaboradorResource extends Resource
{
    protected static ?string $model = BajaColaborador::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $slug = 'bajas-colaboradores';

    protected static ?string $navigationLabel = 'Bajas de colaboradores';

    protected static ?string $modelLabel = 'Baja de colaborador';

    protected static ?string $pluralModelLabel = 'Bajas de colaboradores';

    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::GESTION_PERSONAL;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return BajaColaboradorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BajasColaboradoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBajasColaboradores::route('/'),
            // Rutas literales antes de /{record}: si no, "importar" se toma como {record} y responde 404.
            'importar' => ImportarBajas::route('/importar'),
            'view' => ViewBajaColaborador::route('/{record}'),
            'edit' => EditBajaColaborador::route('/{record}/edit'),
            'reingresar' => ReingresarColaborador::route('/{record}/reingresar'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            $query->where('empresa_id', $tenant->id);
        }

        return $query->with([
            'colaborador',
            'departamento',
            'puesto',
        ]);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:BajaColaborador');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        if (! auth()->user()?->can('Update:BajaColaborador')) {
            return false;
        }

        return $record instanceof BajaColaborador && $record->esProgramada();
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canRestore(Model $record): bool
    {
        return false;
    }

    public static function canRestoreAny(): bool
    {
        return false;
    }

    public static function canReplicate(Model $record): bool
    {
        return false;
    }

    public static function canReorder(): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:BajaColaborador');
    }
}
