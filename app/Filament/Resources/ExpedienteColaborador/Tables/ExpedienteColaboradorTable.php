<?php

declare(strict_types=1);

namespace App\Filament\Resources\ExpedienteColaborador\Tables;

use App\Models\Empresa;
use App\Models\Industria;
use App\Models\Subindustria;
use App\Support\PortafolioColaboradorOpciones;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpedienteColaboradorTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Empresas')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['industria', 'subindustria', 'opcionesPortafolio'])->orderBy('nombre'))
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('industria.nombre')
                    ->label('Industria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            Industria::select('nombre')->whereColumn('industrias.id', 'empresas.industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('subindustria.nombre')
                    ->label('Subindustria')
                    ->searchable()
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderBy(
                            Subindustria::select('nombre')->whereColumn('sub_industrias.id', 'empresas.sub_industria_id'),
                            $direction
                        );
                    }),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('opciones_expediente')
                    ->label('Opciones')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->modalHeading(fn (Empresa $record): string => 'Expediente del colaborador — '.$record->nombre)
                    ->modalDescription('Activa o desactiva los campos visibles en el expediente del colaborador.')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalSubmitActionLabel('Guardar')
                    ->fillForm(fn (Empresa $record): array => PortafolioColaboradorOpciones::defaultsParaEmpresa($record))
                    ->schema([
                        Section::make('Campos del expediente')
                            ->schema(PortafolioColaboradorOpciones::togglesFormulario())
                            ->columns(2),
                    ])
                    ->authorize('update')
                    ->action(function (array $data, Empresa $record): void {
                        PortafolioColaboradorOpciones::sincronizar($record, $data);
                        Notification::make()
                            ->title('Expediente del colaborador actualizado')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
