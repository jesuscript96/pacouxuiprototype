<?php

use App\Http\Controllers\Cliente\ExportarDestinatariosDocumentosCorporativosController;
use App\Http\Controllers\DescargarErroresImportacionController;
use App\Http\Controllers\DescargarPlantillaBajasColaboradoresController;
use App\Http\Controllers\DescargarPlantillaColaboradoresController;
use App\Http\Controllers\PostulacionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/cliente');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/cliente/descargar-plantilla/{empresa}', DescargarPlantillaColaboradoresController::class)
        ->name('cliente.plantilla.colaboradores');
    Route::get('/cliente/descargar-plantilla-bajas/{empresa}', DescargarPlantillaBajasColaboradoresController::class)
        ->name('cliente.plantilla.bajas-colaboradores');
    Route::get('/cliente/importaciones/{importacion}/errores', DescargarErroresImportacionController::class)
        ->name('cliente.importaciones.descargar-errores');
    Route::get('/cliente/documentos-corporativos/destinatarios/exportar/{k}', ExportarDestinatariosDocumentosCorporativosController::class)
        ->middleware('signed')
        ->name('cliente.documentos-corporativos.destinatarios.exportar');
});

// Postulación pública (sin autenticación)
Route::prefix('postular')->name('postulacion.')->group(function (): void {
    Route::get('/{empresa}/{vacante:slug}', [PostulacionController::class, 'mostrar'])
        ->name('formulario')
        ->withoutScopedBindings();
    Route::post('/{empresa}/{vacante:slug}', [PostulacionController::class, 'enviar'])
        ->name('enviar')
        ->withoutScopedBindings();
    Route::get('/confirmacion', [PostulacionController::class, 'confirmacion'])
        ->name('confirmacion');
});
