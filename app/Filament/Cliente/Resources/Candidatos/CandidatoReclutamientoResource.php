<?php

namespace App\Filament\Cliente\Resources\Candidatos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use App\Filament\Cliente\Resources\Candidatos\Pages\ListCandidatos;
use App\Filament\Cliente\Resources\Candidatos\Pages\ViewCandidato;
use App\Filament\Cliente\Resources\Candidatos\Schemas\CandidatoInfolist;
use App\Filament\Cliente\Resources\Candidatos\Tables\CandidatosTable;
use App\Models\CandidatoReclutamiento;
use App\Models\Empresa;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class CandidatoReclutamientoResource extends Resource
{
    protected static ?string $model = CandidatoReclutamiento::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?string $navigationLabel = 'Candidatos';

    protected static ?string $modelLabel = 'Candidato';

    protected static ?string $pluralModelLabel = 'Candidatos';

    protected static ?string $slug = 'candidatos-reclutamiento';

    // BL: candidatos_reclutamiento no tiene empresa_id directo; filtrar vía vacante.empresa_id.
    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?string $navigationParentItem = UxPrototypeParentNavigationItems::RECLUTAMIENTO;

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return CandidatosTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CandidatoInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidatos::route('/'),
            'view' => ViewCandidato::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with([
                'vacante.camposFormulario',
                'historialEstatus.creadoPor',
                'mensajes.usuario',
            ]);

        $tenant = Filament::getTenant();
        if ($tenant instanceof Empresa) {
            $query->whereHas('vacante', fn (Builder $q): Builder => $q->where('empresa_id', $tenant->id));
        }

        return $query;
    }

    // === Permisos (panel Cliente — sin ShieldPlugin) ===

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:CandidatoReclutamiento');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('Delete:CandidatoReclutamiento');
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('DeleteAny:CandidatoReclutamiento');
    }

    public static function canForceDelete(Model $record): bool
    {
        return (bool) auth()->user()?->can('ForceDelete:CandidatoReclutamiento');
    }

    public static function canForceDeleteAny(): bool
    {
        return (bool) auth()->user()?->can('ForceDeleteAny:CandidatoReclutamiento');
    }

    public static function canRestore(Model $record): bool
    {
        return (bool) auth()->user()?->can('Restore:CandidatoReclutamiento');
    }

    public static function canRestoreAny(): bool
    {
        return (bool) auth()->user()?->can('RestoreAny:CandidatoReclutamiento');
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
        return (bool) auth()->user()?->can('View:CandidatoReclutamiento');
    }
}
