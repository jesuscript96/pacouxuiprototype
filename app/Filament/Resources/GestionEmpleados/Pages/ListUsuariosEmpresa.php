<?php

declare(strict_types=1);

namespace App\Filament\Resources\GestionEmpleados\Pages;

use App\Filament\Resources\GestionEmpleados\GestionEmpleadosResource;
use App\Models\FiltroColaborador;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Gate;

class ListUsuariosEmpresa extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = GestionEmpleadosResource::class;

    public function mount(int|string $record): void
    {
        Gate::authorize('viewAny', FiltroColaborador::class);

        $this->record = $this->resolveRecord($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('volver')
                ->label('Volver al listado de empresas')
                ->url(GestionEmpleadosResource::getUrl('index')),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return 'Usuarios de: '.$this->getRecord()->nombre;
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
            ->query(fn () => User::query()
                ->pertenecenAEmpresa((int) $this->getRecord()->getKey())
                ->orderBy('name'));
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('filtrar')
                    ->label('Filtrar colaboradores')
                    ->icon('heroicon-o-funnel')
                    ->url(fn (User $usuario): string => GestionEmpleadosResource::getUrl('filtrar', [
                        'record' => $this->getRecord(),
                        'usuario' => $usuario,
                    ])),
            ])
            ->emptyStateHeading('Sin usuarios vinculados')
            ->emptyStateDescription('No hay usuarios vinculados a esta empresa.');
    }
}
