<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * BL: Los paneles Filament usan español para acciones, modales y placeholders
 * (filament-actions, filament-forms), independientemente de APP_LOCALE en .env.
 */
class SetFilamentLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(config('app.filament_locale', 'es'));

        return $next($request);
    }
}
