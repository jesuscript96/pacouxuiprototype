<?php

namespace App\Filament\Resources\Felicitaciones;

use App\Filament\Resources\Felicitaciones\Pages\CreateFelicitacion;
use App\Filament\Resources\Felicitaciones\Pages\EditFelicitacion;
use App\Filament\Resources\Felicitaciones\Pages\ListFelicitaciones;
use App\Filament\Resources\Felicitaciones\Pages\ViewFelicitacion;
use App\Filament\Resources\Felicitaciones\Schemas\FelicitacionForm;
use App\Filament\Resources\Felicitaciones\Tables\FelicitacionesTable;
use App\Models\Felicitacion;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FelicitacionResource extends Resource
{
    protected static ?string $model = Felicitacion::class;

    protected static ?string $navigationLabel = 'Felicitaciones';

    protected static ?string $modelLabel = 'Felicitación';

    protected static ?string $pluralModelLabel = 'Felicitaciones';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    public static function form(Schema $schema): Schema
    {
        return FelicitacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FelicitacionesTable::configure($table);
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
            'index' => ListFelicitaciones::route('/'),
            'create' => CreateFelicitacion::route('/create'),
            'view' => ViewFelicitacion::route('/{record}'),
            'edit' => EditFelicitacion::route('/{record}/edit'),
        ];
    }
}
