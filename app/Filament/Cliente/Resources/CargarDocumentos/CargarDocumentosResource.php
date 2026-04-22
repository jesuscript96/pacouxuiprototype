<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\CargarDocumentos;

use App\Filament\Cliente\Resources\CargarDocumentos\Pages\CreateCargarDocumentos;
use App\Filament\Cliente\Resources\CargarDocumentos\Pages\EditCargarDocumentos;
use App\Filament\Cliente\Resources\CargarDocumentos\Pages\ListCargarDocumentos;
use App\Filament\Cliente\Resources\CargarDocumentos\Tables\CarpetasTable;
use App\Models\Carpeta;
use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CargarDocumentosResource extends Resource
{
    protected static ?string $model = Carpeta::class;

    protected static ?string $navigationLabel = 'Cargar documentos';

    protected static ?string $modelLabel = 'Carpeta de documentos';

    protected static ?string $pluralModelLabel = 'Carpetas de documentos';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return CarpetasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCargarDocumentos::route('/'),
            'create' => CreateCargarDocumentos::route('/create'),
            'edit' => EditCargarDocumentos::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('tipo', Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS);

        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            $query->where('empresa_id', $tenant->id);
        }

        return $query->with(['empresa'])->orderByDesc('updated_at');
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:Carpeta');
    }

    public static function canCreate(): bool
    {
        return (bool) auth()->user()?->can('Create:Carpeta');
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->can('Update:Carpeta');
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:Carpeta');
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:Carpeta');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:Carpeta');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:Carpeta');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:Carpeta');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:Carpeta');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:Carpeta');
    }

    public static function canReplicate(Model $record): bool
    {
        return (bool) auth()->user()?->can('Replicate:Carpeta');
    }

    public static function canReorder(): bool
    {
        return (bool) auth()->user()?->can('Reorder:Carpeta');
    }
}
