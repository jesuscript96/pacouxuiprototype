<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exports\ReportesInternos\ReporteCierreMesExport;
use App\Models\ReporteInterno;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DescargarReporteCierreMesController extends Controller
{
    public const string SESSION_KEY = 'reporte_cierre_mes_descarga';

    public function __invoke(Request $request): BinaryFileResponse
    {
        Gate::authorize('viewAny', ReporteInterno::class);

        $payload = $request->session()->pull(self::SESSION_KEY);
        abort_if(! is_array($payload), 404);

        $empresaId = (int) ($payload['empresa_id'] ?? 0);
        abort_unless($empresaId > 0, 404);

        $user = $request->user();
        abort_if($user === null, 403);

        // BL: Panel Admin — administradores de plataforma ven cualquier empresa; el resto solo datos de empresas a las que pertenece.
        $esAdminPlataforma = $user->hasRole(Utils::getSuperAdminName()) || $user->tieneRol('administrador');
        abort_unless($esAdminPlataforma || $user->perteneceAEmpresa($empresaId), 403);

        $ubicacionIds = array_values(array_filter(array_map('intval', $payload['ubicacion_ids'] ?? [])));
        $anios = array_values(array_filter(array_map('intval', $payload['anios'] ?? [])));
        $meses = array_values(array_filter(array_map('intval', $payload['meses'] ?? [])));

        $export = new ReporteCierreMesExport($empresaId, $ubicacionIds, $anios, $meses);

        return Excel::download($export, 'Reporte_Cierre_Mes.xlsx');
    }
}
