<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CartaSuaController;
use App\Http\Controllers\Api\NotificacionesPushController;
use App\Http\Controllers\Api\Webhooks\PalencaWebhookController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {

    Route::prefix('notificaciones-push')->group(function (): void {
        Route::get('/', [NotificacionesPushController::class, 'index']);
        Route::get('/no-leidas/count', [NotificacionesPushController::class, 'countNoLeidas']);
        Route::post('/leer-todas', [NotificacionesPushController::class, 'marcarTodasComoLeidas']);
        Route::get('/{id}', [NotificacionesPushController::class, 'show'])->whereNumber('id');
        Route::post('/{id}/leer', [NotificacionesPushController::class, 'marcarComoLeida'])->whereNumber('id');
    });

    Route::prefix('cartas-sua')->group(function (): void {
        Route::get('/', [CartaSuaController::class, 'index']);
        Route::get('/resumen', [CartaSuaController::class, 'resumen']);
        Route::get('/{id}', [CartaSuaController::class, 'show'])->whereNumber('id');
        Route::get('/{id}/pdf', [CartaSuaController::class, 'descargarPdf'])->whereNumber('id')->name('api.cartas-sua.pdf');
        Route::post('/{id}/visualizar', [CartaSuaController::class, 'registrarVisualizacion'])->whereNumber('id');
        Route::post('/{id}/firmar', [CartaSuaController::class, 'firmar'])->whereNumber('id');
    });

});

// BL: Webhooks — autenticación propia, sin Sanctum
Route::prefix('webhooks')->group(function (): void {
    Route::post('/palenca', [PalencaWebhookController::class, 'handle']);
});
