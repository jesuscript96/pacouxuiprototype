<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('bajas:procesar-programadas')
    ->dailyAt('00:25')
    ->onOneServer()
    ->runInBackground()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/bajas-programadas.log'));

Schedule::command('notificaciones:enviar-programadas')
    ->everyFiveMinutes()
    ->onOneServer()
    ->runInBackground()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/notificaciones-programadas.log'));

// BL: Envío de cuentas a verificación - Sábados cada hora (legacy: send:unverified_accounts)
Schedule::command('verificacion:enviar-pendientes')
    ->saturdays()
    ->hourly()
    ->onOneServer()
    ->runInBackground()
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/verificacion-cuentas.log'));
