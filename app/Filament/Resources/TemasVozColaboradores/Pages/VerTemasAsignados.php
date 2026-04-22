<?php

namespace App\Filament\Resources\TemasVozColaboradores\Pages;

use App\Filament\Resources\SegmentacionVozColaboradores\SegmentacionVozColaboradorResource;
use App\Filament\Resources\TemasVozColaboradores\TemasVozColaboradoresResource;
use App\Models\TemaVozColaborador;
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

class VerTemasAsignados extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = TemasVozColaboradoresResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver a empresas')
                ->url(TemasVozColaboradoresResource::getUrl('index')),
            Action::make('crear_tema')
                ->label('Crear tema voz colaborador')
                ->url(fn (): string => SegmentacionVozColaboradorResource::getUrl('create')),
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
        return 'Temas asignados: '.$this->getRecord()->getAttribute('nombre');
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
            ->query(fn () => $this->getRecord()->temasVozColaboradores()->select('temas_voz_colaboradores.*'));
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
                TextColumn::make('empresaExclusiva.nombre')
                    ->label('Exclusivo para empresa')
                    ->placeholder('General'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (TemaVozColaborador $record): string => SegmentacionVozColaboradorResource::getUrl('edit', ['record' => $record])),
            ])
            ->emptyStateHeading('Sin temas asignados')
            ->emptyStateDescription('Esta empresa no tiene temas de voz asignados.');
    }
}
