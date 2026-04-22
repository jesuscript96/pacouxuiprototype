<?php

namespace App\Filament\Resources\Reconocimientos;

use App\Filament\Resources\Reconocimientos\Pages\CreateReconocimientos;
use App\Filament\Resources\Reconocimientos\Pages\EditReconocimientos;
use App\Filament\Resources\Reconocimientos\Pages\ListReconocimientos;
use App\Filament\Resources\Reconocimientos\Pages\ViewReconocimientos;
use App\Filament\Resources\Reconocimientos\RelationManagers\EmpresasRelationManager;
use App\Filament\Resources\Reconocimientos\Schemas\ReconocimientosForm;
use App\Filament\Resources\Reconocimientos\Tables\ReconocimientosTable;
use App\Models\Reconocmiento;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Resource de Reconocimientos para el panel Admin.
 *
 * Catálogo global de reconocimientos que pueden asignarse a empresas
 * vía pivot (1:N si exclusivo, o a TODAS si no exclusivo).
 * Las imágenes (inicial y final) se almacenan en Wasabi/S3.
 */
class ReconocimientosResource extends Resource
{
    protected static ?string $model = Reconocmiento::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Reconocimientos';

    protected static ?string $modelLabel = 'Reconocimiento';

    protected static ?string $pluralModelLabel = 'Reconocimientos';

    /**
     * Configuración del formulario (delegada a ReconocimientosForm).
     */
    public static function form(Schema $schema): Schema
    {
        return ReconocimientosForm::configure($schema);
    }

    /**
     * Configuración de la tabla (delegada a ReconocimientosTable).
     */
    public static function table(Table $table): Table
    {
        return ReconocimientosTable::configure($table);
    }

    /**
     * Relation managers: pivot de empresas asignadas.
     *
     * @return array<class-string<\Filament\Resources\RelationManagers\RelationManager>>
     */
    public static function getRelations(): array
    {
        return [
            EmpresasRelationManager::class,
        ];
    }

    /**
     * Rutas del resource: listado, crear, ver y editar.
     *
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListReconocimientos::route('/'),
            'create' => CreateReconocimientos::route('/create'),
            'view' => ViewReconocimientos::route('/{record}'),
            'edit' => EditReconocimientos::route('/{record}/edit'),
        ];
    }

    /**
     * Incluye registros soft-deleted en la resolución de rutas
     * para que se puedan ver/restaurar desde el panel.
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
