<?php

namespace App\Filament\Resources\SegmentacionProductos;

use App\Filament\Resources\SegmentacionProductos\Pages\EditarProductoSegmentacion;
use App\Filament\Resources\SegmentacionProductos\Pages\ListSegmentacionProductos;
use App\Filament\Resources\SegmentacionProductos\Pages\VerProductosEmpresa;
use App\Models\Empresa;
use Filament\Resources\Resource;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use UnitEnum;

class SegmentacionProductosResource extends Resource
{
    protected static ?string $model = Empresa::class;

    protected static ?string $navigationLabel = 'Segmentación de Productos';

    protected static ?string $modelLabel = 'Segmentación de Productos';

    protected static ?string $pluralModelLabel = 'Segmentación de Productos';

    protected static bool $isScopedToTenant = false;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $slug = 'segmentacion-productos';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['industria', 'subindustria', 'productos'])->whereHas('productos'))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industria.nombre')
                    ->label('Industria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            \App\Models\Industria::select('nombre')->whereColumn('industrias.id', 'empresas.industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('subindustria.nombre')
                    ->label('Sub industria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            \App\Models\Subindustria::select('nombre')->whereColumn('sub_industrias.id', 'empresas.sub_industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('email_contacto')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productos.nombre')
                    ->label('Productos')
                    ->badge()
                    ->separator(', ')
                    ->wrap(),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('editar')
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading(fn (Empresa $record): string => 'Productos de: '.$record->nombre)
                    ->modalContent(fn (Empresa $record): View => view(
                        'filament.segmentacion-productos.productos-empresa-modal',
                        ['record' => $record->loadMissing('productos')],
                    ))
                    ->modalSubmitAction(false)
                    ->modalWidth(Width::FourExtraLarge)
                    ->action(fn () => null),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSegmentacionProductos::route('/'),
            'productos' => VerProductosEmpresa::route('/{record}/productos'),
            'editar-producto' => EditarProductoSegmentacion::route('/{record}/producto/{producto}/editar'),
        ];
    }
}
