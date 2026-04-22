<?php

namespace App\Http\Controllers;

use App\Models\Importacion;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DescargarErroresImportacionController extends Controller
{
    public function __invoke(Importacion $importacion): StreamedResponse
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }
        if (! $user->perteneceAEmpresa($importacion->empresa_id) && ! $user->hasRole('super_admin')) {
            abort(403);
        }
        if (! $importacion->archivo_errores) {
            abort(404);
        }

        $path = $importacion->archivo_errores;
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }
        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $path,
            'errores_importacion_'.$importacion->id.'.xlsx'
        );
    }
}
