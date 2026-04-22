<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\UsuariosEmpresa\Schemas;

use App\Models\SpatieRole;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class UsuarioEmpresaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información del usuario')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->disabled(),
                        TextInput::make('colaborador_resumen')
                            ->label('Colaborador')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('tipo_display')
                            ->label('Tipo de usuario')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Acceso al panel')
                    ->schema([
                        Toggle::make('acceso_panel_cliente')
                            ->label('Tiene acceso al panel de cliente')
                            ->helperText('Si se desactiva, el usuario pierde acceso a esta empresa.')
                            ->live(),
                    ])
                    ->columnSpanFull(),

                Section::make('Roles')
                    ->description('Selecciona los roles que tendrá este usuario en esta empresa.')
                    ->schema([
                        CheckboxList::make('roles')
                            ->label('')
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->withoutGlobalScopes()
                                    ->where('company_id', Filament::getTenant()?->id)
                                    ->where('guard_name', 'web')
                                    ->where('name', '!=', 'super_admin')
                                    ->orderByRaw('COALESCE(display_name, name)')
                            )
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->getOptionLabelFromRecordUsing(function (SpatieRole $record): string {
                                return $record->display_name !== null && $record->display_name !== ''
                                    ? (string) $record->display_name
                                    : $record->name;
                            }),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
