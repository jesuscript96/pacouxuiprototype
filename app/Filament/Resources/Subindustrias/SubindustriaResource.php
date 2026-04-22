<?php

namespace App\Filament\Resources\Subindustrias;

use App\Filament\Resources\Subindustrias\Pages\CreateSubindustria;
use App\Filament\Resources\Subindustrias\Pages\EditSubindustria;
use App\Filament\Resources\Subindustrias\Pages\ListSubindustrias;
use App\Filament\Resources\Subindustrias\Pages\ViewSubindustria;
use App\Filament\Resources\Subindustrias\Schemas\SubindustriaForm;
use App\Filament\Resources\Subindustrias\Tables\SubindustriasTable;
use App\Models\Subindustria;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class SubindustriaResource extends Resource
{
    protected static ?string $model = Subindustria::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    public static function form(Schema $schema): Schema
    {
        return SubindustriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubindustriasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubindustrias::route('/'),
            'create' => CreateSubindustria::route('/create'),
            'view' => ViewSubindustria::route('/{record}'),
            'edit' => EditSubindustria::route('/{record}/edit'),
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
