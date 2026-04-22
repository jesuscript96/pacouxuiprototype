<?php

namespace App\Filament\Cliente\Resources\Ubicaciones\Schemas;

use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class UbicacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información de la ubicación')
                    ->schema([
                        Hidden::make('empresa_id')
                            ->default(fn () => Filament::getTenant()?->id),
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('cp')
                            ->label('Código postal')
                            ->required()
                            ->maxLength(5),
                        Hidden::make('mostrar_modal_calendly')
                            ->default(true),
                        TextInput::make('registro_patronal_sucursal')
                            ->label('Registro patronal sucursal')
                            ->hint('Número de registro patronal asignado por el IMSS a esta sucursal')
                            ->maxLength(255)
                            ->nullable(),
                        TextInput::make('direccion_imss')
                            ->label('Dirección IMSS')
                            ->hint('Subdelegación del IMSS correspondiente')
                            ->maxLength(255)
                            ->nullable(),
                    ]),

                Section::make('Razones sociales')
                    ->description('Crea nuevas razones sociales y asígnalas a esta ubicación')
                    ->schema([
                        Repeater::make('razones_sociales')
                            ->label('Razones sociales')
                            ->schema(self::razonSocialRepeaterSchema())
                            ->columns(1)
                            ->defaultItems(0)
                            ->addActionLabel('Agregar razón social')
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),
            ])
            ->columns(2);
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private static function razonSocialRepeaterSchema(): array
    {
        return [
            Hidden::make('id'),
            TextInput::make('nombre')
                ->label('Nombre de la razón social')
                ->required()
                ->maxLength(255),
            TextInput::make('rfc')
                ->label('RFC')
                ->maxLength(13)
                ->minLength(12)
                ->required(),
            TextInput::make('cp')
                ->label('Código postal')
                ->numeric()
                ->maxLength(5)
                ->minLength(5)
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set): void {
                    if (blank($state)) {
                        return;
                    }
                    $response = null;
                    if (preg_match("/^(?:0?[1-9]|[1-9]\d|5[0-2])\d{3}$/", (string) $state)) {
                        $url = config('app.sepomex').'/'.$state;
                        $request = @file_get_contents($url);
                        $response = $request ? json_decode($request) : null;
                    }
                    if ($response) {
                        $colonias = [];
                        if (empty($response->estados[0])) {
                            Notification::make()
                                ->title('El código postal no es válido')
                                ->danger()
                                ->send();
                            $set('cp', null);

                            return;
                        }

                        foreach ($response->asentamientos ?? [] as $colonia) {
                            $colonias[$colonia] = $colonia;
                        }
                        $set('alcaldia', $response->municipios[0] ?? '');
                        $set('estado', $response->estados[0] ?? '');
                        $set('api_options_storage', json_encode($colonias));
                    }
                }),
            Hidden::make('api_options_storage'),
            TextInput::make('calle')
                ->label('Calle')
                ->required()
                ->maxLength(255),
            TextInput::make('numero_exterior')
                ->label('Número exterior')
                ->maxLength(10)
                ->required(),
            TextInput::make('numero_interior')
                ->label('Número interior')
                ->maxLength(10)
                ->nullable(),
            Select::make('colonia')
                ->label('Colonia')
                ->options(function (Get $get): array {
                    $storage = $get('api_options_storage');
                    if (blank($storage)) {
                        return [];
                    }
                    $decoded = json_decode($storage, true);

                    return is_array($decoded) ? $decoded : [];
                })
                ->live()
                ->required(),
            TextInput::make('alcaldia')
                ->label('Alcaldía / Municipio')
                ->required()
                ->maxLength(255),
            TextInput::make('estado')
                ->label('Estado')
                ->required()
                ->maxLength(255),
        ];
    }
}
