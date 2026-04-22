<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Tables;

use App\Filament\Resources\Usuarios\UsuarioResource;
use App\Filament\Support\CatalogSlideOver;
use App\Models\User;
use App\Services\UsuarioService;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsuariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('apellido_paterno')
                    ->label('Ap. paterno')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('apellido_materno')
                    ->label('Ap. materno')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->separator(', ')
                    ->formatStateUsing(function (mixed $state): ?string {
                        if (blank($state)) {
                            return null;
                        }

                        if (is_array($state)) {
                            $labels = [];
                            foreach ($state as $item) {
                                if (filled($item)) {
                                    $labels[] = self::labelForTipo((string) $item);
                                }
                            }

                            return $labels === [] ? null : implode(', ', $labels);
                        }

                        return self::labelForTipo((string) $state);
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'administrador' => 'danger',
                        'cliente' => 'primary',
                        'colaborador' => 'success',
                        default => 'gray',
                    })
                    ->searchable(query: function (Builder $query, string $search): void {
                        $like = '%'.addcslashes(mb_strtolower($search), '%_\\').'%';
                        $query->whereRaw('LOWER(CAST(users.tipo AS CHAR)) LIKE ?', [$like]);
                    })
                    ->sortable(),
                TextColumn::make('empresas.nombre')
                    ->label('Empresa')
                    ->badge()
                    ->placeholder('—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('empresas', function (Builder $q) use ($search): void {
                            $q->where('nombre', 'like', '%'.$search.'%');
                        });
                    })
                    ->sortable(),
                IconColumn::make('ver_reportes')
                    ->label('Reportes')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('roles_count')
                    ->label('Roles')
                    ->counts('roles')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'administrador' => 'Administrador',
                        'cliente' => 'Cliente',
                        'colaborador' => 'Colaborador',
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $value = $data['value'] ?? null;
                        if (blank($value)) {
                            return;
                        }

                        $query->whereJsonContains('tipo', $value);
                    }),
                SelectFilter::make('empresa_id')
                    ->relationship('empresa', 'nombre')
                    ->label('Empresa')
                    ->placeholder('Todas'),
            ])
            ->recordActions([
                ActionGroup::make([
                    CatalogSlideOver::editAction()
                        ->mutateRecordDataUsing(fn (array $data, User $record): array => UsuarioResource::mutateRecordDataBeforeFillForModal($record, $data))
                        ->mutateDataUsing(fn (array $data): array => UsuarioResource::mutateFormDataBeforeSaveForModal($data))
                        ->using(function (
                            array $data,
                            \Filament\Actions\Contracts\HasActions&\Filament\Schemas\Contracts\HasSchemas $livewire,
                            User $record,
                            ?Table $table
                        ): void {
                            app(UsuarioService::class)->update($record, $data);
                        }),
                    DeleteAction::make(),
                ])
                    ->tooltip(__('Acciones')),
            ])
            ->paginated(true);
    }

    private static function labelForTipo(string $tipo): string
    {
        return match ($tipo) {
            'administrador' => 'Administrador',
            'cliente' => 'Cliente',
            'colaborador' => 'Colaborador',
            default => $tipo,
        };
    }
}
