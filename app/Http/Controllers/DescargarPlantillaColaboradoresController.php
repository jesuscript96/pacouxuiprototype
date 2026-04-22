<?php

namespace App\Http\Controllers;

use App\Exports\PlantillaColaboradoresExport;
use App\Models\Empresa;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DescargarPlantillaColaboradoresController extends Controller
{
    public function __invoke(Empresa $empresa): BinaryFileResponse
    {
        $user = auth()->user();
        if (! $user?->can('Import:Colaborador')) {
            abort(403);
        }
        if (! $user->perteneceAEmpresa($empresa->id) && ! $user->hasRole('super_admin')) {
            abort(403);
        }

        $export = new PlantillaColaboradoresExport($empresa);
        $filename = 'plantilla_colaboradores_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download($export, $filename);
    }
}
