<?php

namespace App\Filament\Resources\GestionCarruseles;

use App\Filament\Resources\GestionCarruseles\Pages\EditCarruselEmpresa;
use App\Filament\Resources\GestionCarruseles\Pages\ListGestionCarruseles;
use App\Filament\Resources\GestionCarruseles\Tables\GestionCarruselesTable;
use App\Models\Empresa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class GestionCarruselesResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationLabel = 'Gestión de Carruseles';

    protected static ?string $modelLabel = 'Carrusel';

    protected static ?string $pluralModelLabel = 'Gestión de Carruseles';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $slug = 'gestion-carruseles';

    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-photo';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return GestionCarruselesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGestionCarruseles::route('/'),
            'carrusel' => EditCarruselEmpresa::route('/{record}/carrusel'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
