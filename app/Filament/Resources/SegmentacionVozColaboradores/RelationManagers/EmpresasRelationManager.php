<?php

namespace App\Filament\Resources\SegmentacionVozColaboradores\RelationManagers;

use App\Models\Empresa;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Attributes\On;

class EmpresasRelationManager extends RelationManager
{
    protected static string $relationship = 'empresas';

    protected static ?string $title = 'Empresas';

    #[On('refresh-empresas-relation-manager')]
    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    public function table(Table $table): Table
    {
        return $table
            ->inverseRelationship('temasVozColaboradores')
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('email_contacto')
                    ->label('Email contacto')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('attachAll')
                    ->label('Vincular todas')
                    ->color('gray')
                    ->action(function (): void {
                        $owner = $this->ownerRecord;
                        $attachedIds = $owner->empresas->modelKeys();
                        $allIds = Empresa::query()->orderBy('nombre')->pluck('id')->all();
                        $toAttach = array_values(array_diff($allIds, $attachedIds));

                        if ($toAttach === []) {
                            Notification::make()
                                ->title('Todas las empresas ya están vinculadas')
                                ->info()
                                ->send();

                            return;
                        }

                        $owner->empresas()->attach($toAttach);

                        Notification::make()
                            ->title(count($toAttach) === 1
                                ? '1 empresa vinculada'
                                : count($toAttach).' empresas vinculadas')
                            ->success()
                            ->send();
                    }),
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->multiple(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
