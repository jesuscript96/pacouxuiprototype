<?php

namespace App\Filament\Cliente\Resources\Candidatos\Schemas;

use App\Models\CandidatoReclutamiento;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidatoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::seccionResumen(),
                static::seccionDatosFormulario(),
                static::seccionArchivos(),
                static::seccionHistorialLaboral(),
                static::seccionHistorialEstatus(),
                static::seccionComentarios(),
            ]);
    }

    private static function seccionResumen(): Section
    {
        return Section::make('Resumen del candidato')
            ->schema([
                TextEntry::make('nombre_completo')
                    ->label('Nombre completo'),

                TextEntry::make('curp')
                    ->label('CURP')
                    ->placeholder('—'),

                TextEntry::make('email')
                    ->label('Email')
                    ->placeholder('—'),

                TextEntry::make('telefono')
                    ->label('Teléfono')
                    ->placeholder('—'),

                TextEntry::make('vacante.puesto')
                    ->label('Vacante'),

                TextEntry::make('estatus')
                    ->label('Estatus actual')
                    ->badge()
                    ->color(fn (CandidatoReclutamiento $record): string => $record->colorEstatus()),

                TextEntry::make('evaluacion_cv')
                    ->label('Evaluación CV')
                    ->formatStateUsing(fn ($state): string => $state !== null ? "{$state}/10" : '—'),

                TextEntry::make('created_at')
                    ->label('Fecha de postulación')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(4);
    }

    private static function seccionDatosFormulario(): Section
    {
        return Section::make('Datos del formulario')
            ->schema([
                KeyValueEntry::make('valores_formulario_legible')
                    ->hiddenLabel()
                    ->state(function (CandidatoReclutamiento $record): array {
                        $campos = $record->vacante?->camposFormulario ?? collect();
                        $valores = $record->valores_formulario ?? [];

                        $resultado = [];
                        foreach ($campos as $campo) {
                            if ($campo->tipo === 'file') {
                                continue;
                            }

                            $valor = $valores[$campo->nombre] ?? null;

                            $resultado[$campo->etiqueta] = match (true) {
                                is_array($valor) => implode(', ', $valor),
                                $valor === null, $valor === '' => '—',
                                default => (string) $valor,
                            };
                        }

                        return $resultado;
                    })
                    ->columnSpanFull(),
            ])
            ->collapsible();
    }

    private static function seccionArchivos(): Section
    {
        return Section::make('Archivos')
            ->schema([
                RepeatableEntry::make('archivos_info')
                    ->hiddenLabel()
                    ->state(function (CandidatoReclutamiento $record): array {
                        $archivos = $record->archivos ?? [];
                        if ($archivos === []) {
                            return [];
                        }

                        $campos = $record->vacante?->camposFormulario
                            ?->where('tipo', 'file')
                            ->keyBy('nombre') ?? collect();

                        $resultado = [];
                        foreach ($archivos as $nombre => $info) {
                            $campo = $campos->get($nombre);
                            $path = $info['path'] ?? null;
                            $isValid = $info['is_valid'] ?? null;

                            $resultado[] = [
                                'campo_nombre' => $nombre,
                                'campo' => $campo?->etiqueta ?? $nombre,
                                'nombre_original' => $info['nombre_original'] ?? basename($path ?? ''),
                                'estado' => match (true) {
                                    $path === null => 'No subido',
                                    $isValid === null => 'Pendiente de validación',
                                    $isValid === true => 'Válido',
                                    default => 'Inválido',
                                },
                                'tiene_archivo' => $path !== null,
                            ];
                        }

                        return $resultado;
                    })
                    ->schema([
                        TextEntry::make('campo')
                            ->label('Campo'),
                        TextEntry::make('nombre_original')
                            ->label('Archivo'),
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Válido' => 'success',
                                'Inválido' => 'danger',
                                'Pendiente de validación' => 'warning',
                                default => 'gray',
                            }),
                        IconEntry::make('tiene_archivo')
                            ->label('Subido')
                            ->boolean(),
                    ])
                    ->columns(4),
            ])
            ->collapsible()
            ->visible(fn (CandidatoReclutamiento $record): bool => ! empty($record->archivos));
    }

    private static function seccionHistorialLaboral(): Section
    {
        return Section::make('Historial Laboral IMSS')
            ->schema([
                TextEntry::make('historialLaboral.estatus_laboral')
                    ->label('Estatus laboral')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'EMPLEADO' ? 'success' : 'warning')
                    ->placeholder('—'),

                TextEntry::make('historialLaboral.empresa_actual')
                    ->label('Empresa actual')
                    ->placeholder('—'),

                TextEntry::make('historialLaboral.semanas_cotizadas')
                    ->label('Semanas cotizadas')
                    ->placeholder('—'),

                TextEntry::make('historialLaboral.nss')
                    ->label('NSS')
                    ->placeholder('—'),

                TextEntry::make('historialLaboral.account_status')
                    ->label('Estado de consulta')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'COMPLETED' => 'success',
                        'PENDING' => 'warning',
                        'FAILED' => 'danger',
                        default => 'gray',
                    })
                    ->placeholder('—'),

                TextEntry::make('historialLaboral.ultima_actualizacion')
                    ->label('Última actualización')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->columns(3)
            ->collapsible()
            ->visible(fn (CandidatoReclutamiento $record): bool => $record->historialLaboral !== null);
    }

    private static function seccionHistorialEstatus(): Section
    {
        return Section::make('Historial de estatus')
            ->schema([
                RepeatableEntry::make('historialEstatus')
                    ->hiddenLabel()
                    ->schema([
                        TextEntry::make('estatus')
                            ->label('Estatus')
                            ->badge()
                            ->color(fn (string $state): string => CandidatoReclutamiento::ESTATUS_COLORES[$state] ?? 'gray'),

                        TextEntry::make('fecha_inicio')
                            ->label('Inicio')
                            ->dateTime('d/m/Y H:i'),

                        TextEntry::make('fecha_fin')
                            ->label('Fin')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('Activo'),

                        TextEntry::make('duracion')
                            ->label('Duración')
                            ->placeholder('—'),

                        TextEntry::make('creadoPor.name')
                            ->label('Registrado por')
                            ->placeholder('Sistema'),
                    ])
                    ->columns(5),
            ])
            ->collapsible();
    }

    private static function seccionComentarios(): Section
    {
        return Section::make('Comentarios')
            ->schema([
                RepeatableEntry::make('mensajes')
                    ->hiddenLabel()
                    ->schema([
                        TextEntry::make('comentario')
                            ->label('Comentario')
                            ->columnSpanFull(),

                        TextEntry::make('usuario.name')
                            ->label('Usuario'),

                        TextEntry::make('created_at')
                            ->label('Fecha')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ])
            ->collapsible();
    }
}
