<?php

namespace App\Filament\Resources\SegmentacionProductos\Pages;

use App\Filament\Resources\SegmentacionProductos\SegmentacionProductosResource;
use App\Models\Producto;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

class VerProductosEmpresa extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = SegmentacionProductosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a empresas')
                ->url(SegmentacionProductosResource::getUrl('index')),
        ];
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
    }

    protected function authorizeAccess(): void
    {
        //
    }

    public function getTitle(): string|Htmlable
    {
        return 'Productos de: '.$this->getRecord()->getAttribute('nombre');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                EmbeddedTable::make(),
            ]);
    }

    protected function makeTable(): Table
    {
        return Table::make($this)
            ->query(fn () => $this->getRecord()->productos()->select('productos.*'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(40)
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->url(fn (Producto $record): string => SegmentacionProductosResource::getUrl('editar-producto', [
                        'record' => $this->getRecord()->getKey(),
                        'producto' => $record->getKey(),
                    ])),
            ])
            ->emptyStateHeading('Sin productos asignados')
            ->emptyStateDescription('Esta empresa no tiene productos.');
    }
}
