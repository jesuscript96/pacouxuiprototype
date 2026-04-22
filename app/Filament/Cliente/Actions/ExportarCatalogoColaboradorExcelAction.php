<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Actions;

use App\Exports\CatalogoColaboradorFilasExport;
use Filament\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

final class ExportarCatalogoColaboradorExcelAction
{
    /**
     * @param  array<int, string>  $encabezados
     * @param  callable(Builder): array<int, array<int, mixed>>  $mapearFilas
     */
    public static function make(
        string $permisoViewAny,
        string $nombreArchivoBase,
        string $tituloHoja,
        array $encabezados,
        callable $mapearFilas,
    ): Action {
        return Action::make('exportar_excel')
            ->label('Exportar a Excel')
            ->icon('heroicon-o-arrow-down-tray')
            ->visible(fn (): bool => (bool) auth()->user()?->can($permisoViewAny))
            ->action(function (Action $action) use ($nombreArchivoBase, $tituloHoja, $encabezados, $mapearFilas): mixed {
                $livewire = $action->getLivewire();
                if (! $livewire instanceof HasTable) {
                    return null;
                }

                $consulta = $livewire->getFilteredTableQuery();
                if (! $consulta instanceof Builder) {
                    return null;
                }

                $filas = $mapearFilas(clone $consulta);

                return Excel::download(
                    new CatalogoColaboradorFilasExport($tituloHoja, $encabezados, $filas),
                    $nombreArchivoBase.'-'.now()->format('Y-m-d').'.xlsx',
                );
            });
    }
}
