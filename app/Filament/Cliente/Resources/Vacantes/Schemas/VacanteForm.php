<?php

namespace App\Filament\Cliente\Resources\Vacantes\Schemas;

use App\Models\CampoFormularioVacante;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VacanteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Vacante')
                    ->tabs([
                        Tab::make('Información')
                            ->icon('heroicon-o-information-circle')
                            ->schema(static::informacionSchema()),

                        Tab::make('Formulario de Postulación')
                            ->icon('heroicon-o-document-text')
                            ->schema(static::formularioSchema()),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /** @return array<int, mixed> */
    private static function informacionSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    TextInput::make('puesto')
                        ->label('Puesto / Título de la vacante')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->disabled()
                        ->dehydrated()
                        ->helperText('Se genera automáticamente desde el puesto'),

                    RichEditor::make('requisitos')
                        ->label('Requisitos')
                        ->required()
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                            'h2', 'h3',
                        ])
                        ->columnSpanFull(),

                    RichEditor::make('aptitudes')
                        ->label('Aptitudes')
                        ->required()
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                        ])
                        ->columnSpanFull(),

                    RichEditor::make('prestaciones')
                        ->label('Prestaciones / Beneficios')
                        ->required()
                        ->toolbarButtons([
                            'bold', 'italic', 'underline',
                            'bulletList', 'orderedList',
                        ])
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    /** @return array<int, mixed> */
    private static function formularioSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Repeater::make('camposFormulario')
                        ->relationship('camposFormulario')
                        ->label('Campos del formulario de postulación')
                        ->schema([
                            Select::make('tipo')
                                ->label('Tipo de campo')
                                ->options(CampoFormularioVacante::TIPOS)
                                ->required()
                                ->live()
                                ->columnSpan(1),

                            TextInput::make('etiqueta')
                                ->label('Etiqueta')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state): void {
                                    $set('nombre', Str::slug($state, '_'));
                                })
                                ->columnSpan(1),

                            TextInput::make('nombre')
                                ->label('Nombre técnico')
                                ->disabled()
                                ->dehydrated()
                                ->helperText('Identificador único del campo')
                                ->columnSpan(1),

                            Toggle::make('requerido')
                                ->label('¿Es requerido?')
                                ->default(false)
                                ->columnSpan(1),

                            TextInput::make('placeholder')
                                ->label('Placeholder')
                                ->maxLength(255)
                                ->columnSpan(1),

                            TextInput::make('tipos_archivo')
                                ->label('Tipos de archivo permitidos')
                                ->placeholder('image/png,image/jpeg,application/pdf')
                                ->helperText('MIME types separados por coma')
                                ->visible(fn (Get $get): bool => $get('tipo') === 'file')
                                ->columnSpan(1),

                            TextInput::make('longitud_minima')
                                ->label('Longitud mínima')
                                ->numeric()
                                ->minValue(0)
                                ->visible(fn (Get $get): bool => in_array($get('tipo'), ['text', 'textarea'], true))
                                ->columnSpan(1),

                            TextInput::make('longitud_maxima')
                                ->label('Longitud máxima')
                                ->numeric()
                                ->minValue(1)
                                ->visible(fn (Get $get): bool => in_array($get('tipo'), ['text', 'textarea'], true))
                                ->columnSpan(1),

                            TagsInput::make('opciones')
                                ->label('Opciones')
                                ->placeholder('Agregar opción y presionar Enter')
                                ->helperText('Opciones para el campo de selección')
                                ->visible(fn (Get $get): bool => $get('tipo') === 'select')
                                ->columnSpanFull(),

                            Section::make('Campo condicional')
                                ->schema([
                                    Toggle::make('es_dependiente')
                                        ->label('¿Es un campo condicional?')
                                        ->live()
                                        ->helperText('Se mostrará solo si otro campo tiene un valor específico'),

                                    TextInput::make('campo_padre')
                                        ->label('Campo padre (nombre técnico)')
                                        ->visible(fn (Get $get): bool => (bool) $get('es_dependiente')),

                                    TextInput::make('valor_activador')
                                        ->label('Valor que lo activa')
                                        ->visible(fn (Get $get): bool => (bool) $get('es_dependiente')),
                                ])
                                ->columns(3),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->reorderable()
                        ->orderColumn('orden')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['etiqueta'] ?? 'Nuevo campo')
                        ->addActionLabel('Agregar campo')
                        ->columnSpanFull(),
                ])
                ->columnSpanFull(),
        ];
    }
}
