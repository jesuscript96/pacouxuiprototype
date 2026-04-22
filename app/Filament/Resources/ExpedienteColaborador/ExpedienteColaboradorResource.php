<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExpedienteColaborador;

use App\Filament\Resources\ExpedienteColaborador\Pages\ListExpedienteColaborador;
use App\Filament\Resources\ExpedienteColaborador\Tables\ExpedienteColaboradorTable;
use App\Models\Empresa;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ExpedienteColaboradorResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationLabel = 'Expediente del colaborador';

    protected static ?string $modelLabel = 'Empresa';

    protected static ?string $pluralModelLabel = 'Expediente del colaborador';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $slug = 'expediente-colaborador';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ExpedienteColaboradorTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExpedienteColaborador::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canForceDelete($record): bool
    {
        return false;
    }

    public static function canRestore($record): bool
    {
        return false;
    }
}
