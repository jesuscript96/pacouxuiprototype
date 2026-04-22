<?php

namespace App\Filament\Resources\Industrias;

use App\Filament\Resources\Industrias\Pages\CreateIndustria;
use App\Filament\Resources\Industrias\Pages\EditIndustria;
use App\Filament\Resources\Industrias\Pages\ListIndustrias;
use App\Filament\Resources\Industrias\Pages\ViewIndustria;
use App\Filament\Resources\Industrias\Schemas\IndustriaForm;
use App\Filament\Resources\Industrias\Tables\IndustriasTable;
use App\Models\Industria;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class IndustriaResource extends Resource
{
    protected static ?string $model = Industria::class;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    public static function form(Schema $schema): Schema
    {
        return IndustriaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndustriasTable::configure($table);
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
            'index' => ListIndustrias::route('/'),
            'create' => CreateIndustria::route('/create'),
            'view' => ViewIndustria::route('/{record}'),
            'edit' => EditIndustria::route('/{record}/edit'),
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
