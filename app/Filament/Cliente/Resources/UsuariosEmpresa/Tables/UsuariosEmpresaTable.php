<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\UsuariosEmpresa\Tables;

use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UsuariosEmpresaTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('colaborador.numero_colaborador')
                    ->label('No. Colaborador')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->formatStateUsing(function (mixed $state): string {
                        if (! is_array($state)) {
                            return (string) $state;
                        }

                        return implode(', ', array_map('ucfirst', $state));
                    })
                    ->badge()
                    ->color('gray'),
                TextColumn::make('roles_resumen')
                    ->label('Roles')
                    ->getStateUsing(function (User $record): string {
                        $labels = $record->roles
                            ->map(fn ($role) => $role->display_name ?: $role->name)
                            ->unique()
                            ->values()
                            ->all();

                        return count($labels) > 0 ? implode(', ', $labels) : 'Sin rol';
                    }),
                IconColumn::make('acceso_panel')
                    ->label('Acceso')
                    ->getStateUsing(fn (User $record): bool => is_array($record->tipo) && in_array('cliente', $record->tipo, true))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Rol')
                    ->relationship(
                        name: 'roles',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query
                            ->withoutGlobalScopes()
                            ->where('company_id', Filament::getTenant()?->id)
                            ->where('guard_name', 'web')
                    )
                    ->multiple()
                    ->preload(),
                TernaryFilter::make('acceso_panel')
                    ->label('Acceso al panel')
                    ->queries(
                        true: fn (Builder $query) => $query->whereJsonContains('tipo', 'cliente'),
                        false: fn (Builder $query) => $query->whereJsonDoesntContain('tipo', 'cliente'),
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar acceso'),
            ])
            ->emptyStateHeading('No hay usuarios')
            ->emptyStateDescription('Los usuarios con acceso suelen crearse al dar de alta colaboradores o desde administración.')
            ->paginated(true);
    }
}
