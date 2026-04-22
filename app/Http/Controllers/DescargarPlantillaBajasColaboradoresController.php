<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\BajasColaboradores\PlantillaBajasExport;
use App\Models\Empresa;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DescargarPlantillaBajasColaboradoresController extends Controller
{
    public function __invoke(Empresa $empresa): BinaryFileResponse
    {
        $user = auth()->user();
        if (! $user?->can('Create:BajaColaborador')) {
            abort(403);
        }
        if (! $user->perteneceAEmpresa($empresa->id) && ! $user->hasRole('super_admin')) {
            abort(403);
        }

        return Excel::download(
            new PlantillaBajasExport,
            'plantilla_bajas_colaboradores.xlsx'
        );
    }
}
