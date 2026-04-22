<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores;

use App\Filament\Resources\SegmentacionVozColaboradores\Pages\CreateSegmentacionVozColaborador;
use App\Filament\Resources\SegmentacionVozColaboradores\Pages\EditSegmentacionVozColaborador;
use App\Filament\Resources\SegmentacionVozColaboradores\Pages\ListSegmentacionVozColaboradores;
use App\Filament\Resources\SegmentacionVozColaboradores\RelationManagers\EmpresasRelationManager;
use App\Filament\Resources\SegmentacionVozColaboradores\Schemas\SegmentacionVozColaboradorForm;
use App\Filament\Resources\SegmentacionVozColaboradores\Tables\SegmentacionVozColaboradoresTable;
use App\Models\TemaVozColaborador;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SegmentacionVozColaboradorResource extends Resource
{
    protected static ?string $model = TemaVozColaborador::class;

    protected static ?string $slug = 'segmentacion-voz-colaboradores';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Segmentación Voz del Colaborador';

    protected static ?string $modelLabel = 'Segmentación Voz del Colaborador';

    protected static ?string $pluralModelLabel = 'Segmentación Voz del Colaborador';

    public static function form(Schema $schema): Schema
    {
        return SegmentacionVozColaboradorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SegmentacionVozColaboradoresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            EmpresasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegmentacionVozColaboradores::route('/'),
            'create' => CreateSegmentacionVozColaborador::route('/create'),
            'edit' => EditSegmentacionVozColaborador::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
