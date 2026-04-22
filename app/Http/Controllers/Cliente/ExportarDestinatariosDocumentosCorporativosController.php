<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cliente;

use App\Exports\DocumentosCorporativosDestinatariosExport;
use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Services\DocumentosCorporativosDestinatariosConsultaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportarDestinatariosDocumentosCorporativosController extends Controller
{
    public function __invoke(Request $solicitud, string $k): BinaryFileResponse
    {
        $usuario = $solicitud->user();
        if (! $usuario) {
            abort(403);
        }

        if (! $usuario->can('ViewAny:DocumentoCorporativo')) {
            abort(403);
        }

        /** @var array{user_id: int, empresa_id: int, filters: array<string, mixed>, search: string|null}|null $carga */
        $carga = Cache::pull('export_doc_dest_'.$k);

        if (
            $carga === null
            || (int) $carga['user_id'] !== (int) $usuario->id
        ) {
            abort(403);
        }

        if (
            ! $usuario->perteneceAEmpresa((int) $carga['empresa_id'])
            && ! $usuario->hasRole('super_admin')
        ) {
            abort(403);
        }

        $empresa = Empresa::query()->findOrFail((int) $carga['empresa_id']);

        $consulta = DocumentosCorporativosDestinatariosConsultaService::consultaFiltrada(
            $empresa,
            $carga['filters'] ?? [],
            $carga['search'] ?? null,
        );

        $nombreArchivo = 'destinatarios_documentos_corporativos_'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new DocumentosCorporativosDestinatariosExport($consulta),
            $nombreArchivo,
        );
    }
}
