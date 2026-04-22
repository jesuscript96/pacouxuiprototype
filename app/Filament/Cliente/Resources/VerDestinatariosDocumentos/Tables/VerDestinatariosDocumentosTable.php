<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\VerDestinatariosDocumentos\Tables;

use App\Models\Carpeta;
use App\Models\DocumentoCorporativo;
use App\Models\Empresa;
use App\Services\DocumentosCorporativosDestinatariosConsultaService;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class VerDestinatariosDocumentosTable
{
    public static function configure(Table $tabla): Table
    {
        return $tabla
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->getStateUsing(fn (DocumentoCorporativo $registro): string => $registro->user?->colaborador?->nombre_completo ?? $registro->user?->name ?? '—')
                    ->sortable(),
                TextColumn::make('user.empresa.nombre')
                    ->label('Empresa')
                    ->placeholder('—')
                    ->limit(120)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('carpeta.nombre')
                    ->label('Carpeta')
                    ->sortable(),
                TextColumn::make('subcarpeta')
                    ->label('Subcarpeta')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('nombre_documento')
                    ->label('Documento')
                    ->sortable(),
                TextColumn::make('fecha_carga')
                    ->label('Fecha de carga')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('primera_visualizacion')
                    ->label('Primera visualización')
                    ->badge()
                    ->getStateUsing(function (DocumentoCorporativo $registro): string {
                        $fecha = $registro->primera_visualizacion;

                        return $fecha === null
                            ? 'No visualizado'
                            : $fecha->format('d/m/Y H:i');
                    })
                    ->color(fn (string $state): string => $state === 'No visualizado' ? 'danger' : 'success')
                    ->sortable(),
                TextColumn::make('ultima_visualizacion')
                    ->label('Última visualización')
                    ->badge()
                    ->getStateUsing(function (DocumentoCorporativo $registro): string {
                        $fecha = $registro->ultima_visualizacion;

                        return $fecha === null
                            ? 'No visualizado'
                            : $fecha->format('d/m/Y H:i');
                    })
                    ->color(fn (string $state): string => $state === 'No visualizado' ? 'danger' : 'success')
                    ->sortable(),
            ])
            ->filters(
                [
                    SelectFilter::make('carpeta_id')
                        ->label('Carpeta')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(function (Component $livewire): array {
                            $inquilino = Filament::getTenant();
                            if (! $inquilino instanceof Empresa) {
                                return [];
                            }

                            $consulta = Carpeta::query()
                                ->where('empresa_id', $inquilino->id)
                                ->where('tipo', Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS)
                                ->orderBy('nombre');

                            $nombresDocumento = data_get($livewire->tableFilters, 'nombre_documento.values');
                            if (filled($nombresDocumento) && is_array($nombresDocumento)) {
                                $consulta->whereHas('documentosCorporativos', function (Builder $relacion) use ($nombresDocumento): void {
                                    $relacion->whereIn('nombre_documento', $nombresDocumento);
                                });
                            }

                            return $consulta->pluck('nombre', 'id')->all();
                        }),
                    SelectFilter::make('nombre_documento')
                        ->label('Documento')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->options(function (Component $livewire): array {
                            $inquilino = Filament::getTenant();
                            if (! $inquilino instanceof Empresa) {
                                return [];
                            }

                            $consulta = DocumentoCorporativo::query()
                                ->whereHas('carpeta', function (Builder $carpeta) use ($inquilino): void {
                                    $carpeta
                                        ->where('empresa_id', $inquilino->id)
                                        ->where('tipo', Carpeta::TIPO_DOCUMENTOS_CORPORATIVOS);
                                })
                                ->whereHas('user', function (Builder $user) use ($inquilino): void {
                                    $user->where('empresa_id', $inquilino->id);
                                });

                            $idsCarpetas = data_get($livewire->tableFilters, 'carpeta_id.values');
                            if (filled($idsCarpetas) && is_array($idsCarpetas)) {
                                $consulta->whereIn('documentos_corporativos.carpeta_id', $idsCarpetas);
                            }

                            $nombres = $consulta
                                ->distinct()
                                ->orderBy('nombre_documento')
                                ->pluck('nombre_documento');

                            return $nombres
                                ->mapWithKeys(fn (string $nombre): array => [$nombre => $nombre])
                                ->all();
                        }),
                ],
                layout: FiltersLayout::AboveContent,
            )
            ->filtersFormColumns(2)
            ->searchPlaceholder('Buscar por usuario, carpeta, documento o escriba «no visualizado»')
            ->splitSearchTerms(false)
            ->searchUsing(function (Builder $consulta, string $busqueda): void {
                DocumentosCorporativosDestinatariosConsultaService::aplicarBusqueda($consulta, $busqueda);
            })
            ->defaultSort('fecha_carga', 'desc')
            ->recordActions([])
            ->toolbarActions([])
            ->paginated([10, 25, 50, 100]);
    }
}
