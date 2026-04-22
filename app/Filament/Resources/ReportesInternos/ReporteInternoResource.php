<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReportesInternos;

use App\Filament\Resources\ReportesInternos\Pages\GenerarReporteCierreMes;
use App\Models\ReporteInterno;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ReporteInternoResource extends Resource
{
    protected static ?string $model = ReporteInterno::class;

    protected static ?string $navigationLabel = 'Reportes internos';

    protected static ?string $modelLabel = 'Reporte interno';

    protected static ?string $pluralModelLabel = 'Reportes internos';

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    // protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => GenerarReporteCierreMes::route('/'),
        ];
    }
}
