<?php

namespace App\Filament\Resources\NotificacionesIncluidas;

use App\Filament\Resources\NotificacionesIncluidas\Pages\CreateNotificacionesIncluidas;
use App\Filament\Resources\NotificacionesIncluidas\Pages\EditNotificacionesIncluidas;
use App\Filament\Resources\NotificacionesIncluidas\Pages\ListNotificacionesIncluidas;
use App\Filament\Resources\NotificacionesIncluidas\Pages\ViewNotificacionesIncluidas;
use App\Filament\Resources\NotificacionesIncluidas\Schemas\NotificacionesIncluidasForm;
use App\Filament\Resources\NotificacionesIncluidas\Tables\NotificacionesIncluidasTable;
use App\Models\NotificacionesIncluidas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class NotificacionesIncluidasResource extends Resource
{
    protected static ?string $model = NotificacionesIncluidas::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    public static function form(Schema $schema): Schema
    {
        return NotificacionesIncluidasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacionesIncluidasTable::configure($table);
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
            'index' => ListNotificacionesIncluidas::route('/'),
            'create' => CreateNotificacionesIncluidas::route('/create'),
            'view' => ViewNotificacionesIncluidas::route('/{record}'),
            'edit' => EditNotificacionesIncluidas::route('/{record}/edit'),
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
