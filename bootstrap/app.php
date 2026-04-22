<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // BL: Tras balanceador/terminación TLS (X-Forwarded-Proto) para cookies Secure y detección HTTPS.
        $middleware->trustProxies(at: '*');

        $middleware->redirectGuestsTo(fn (): string => route('filament.cliente.auth.login'));

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\ScopeByCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sin manejo especial de 403: el prototipo no tiene login ni panel admin.
    })->create();
