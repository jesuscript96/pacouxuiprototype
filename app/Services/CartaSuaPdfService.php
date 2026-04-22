<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CartaSua;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CartaSuaPdfService
{
    public function __construct(
        private ArchivoService $archivoService,
    ) {}

    /**
     * Generar PDF de una carta SUA y guardarlo en storage.
     *
     * @param  array<string, mixed>|null  $datosExcel  Datos del Excel (generación inicial)
     * @return string Ruta relativa del archivo guardado
     */
    public function generar(CartaSua $carta, ?array $datosExcel = null): string
    {
        $datos = $this->prepararDatos($carta, $datosExcel);

        $pdf = Pdf::loadView('exports.pdf.carta-sua', $datos)
            ->setPaper('letter', 'portrait');

        $path = $this->archivoService->guardarContenido(
            contenido: $pdf->output(),
            empresa: $carta->empresa,
            modulo: 'cartas-sua',
            registroId: $carta->id,
            nombreConExtension: 'carta.pdf',
        );

        $carta->update(['pdf_path' => $path]);

        Log::info('PDF de Carta SUA generado', [
            'carta_id' => $carta->id,
            'colaborador_id' => $carta->colaborador_id,
            'path' => $path,
        ]);

        return $path;
    }

    /**
     * Regenerar PDF de una carta existente.
     * Elimina el PDF anterior y genera uno nuevo.
     */
    public function regenerar(CartaSua $carta): string
    {
        if ($carta->pdf_path && $this->archivoService->existe($carta->pdf_path)) {
            $this->archivoService->eliminar($carta->pdf_path);
        }

        return $this->generar($carta);
    }

    /**
     * URL firmada temporal para preview/descarga del PDF.
     */
    public function obtenerUrl(CartaSua $carta, int $minutos = 60): string
    {
        if (! $carta->pdf_path) {
            return '';
        }

        return $this->archivoService->url($carta->pdf_path, $minutos);
    }

    /**
     * Descargar PDF como StreamedResponse.
     */
    public function descargar(CartaSua $carta): StreamedResponse
    {
        $nombreArchivo = sprintf(
            'carta-sua-%s-%s.pdf',
            $carta->colaborador_id,
            str_replace(' ', '-', $carta->bimestre),
        );

        return $this->archivoService->descargar($carta->pdf_path, $nombreArchivo);
    }

    /**
     * BL: Prioridad de datos para el PDF: Excel > datos_origen > modelo Colaborador.
     *
     * @param  array<string, mixed>|null  $datosExcel
     * @return array<string, mixed>
     */
    private function prepararDatos(CartaSua $carta, ?array $datosExcel = null): array
    {
        $origen = $datosExcel ?? $carta->datos_origen ?? [];

        $carta->loadMissing(['colaborador', 'empresa']);

        return [
            'nombre_empleado' => $origen['nombre']
                ?? $origen['nombre_empleado']
                ?? $carta->colaborador->nombre_completo
                ?? '',
            'rfc' => $origen['rfc']
                ?? $carta->colaborador->rfc
                ?? '',
            'curp' => $origen['curp']
                ?? $carta->colaborador->curp
                ?? '',
            'razon_social' => $carta->razon_social,
            'bimestre' => $carta->bimestre,
            'retiro' => number_format((float) $carta->retiro, 2, '.', ','),
            'cesantia_vejez' => number_format((float) $carta->cesantia_vejez, 2, '.', ','),
            'infonavit' => number_format((float) $carta->infonavit, 2, '.', ','),
            'total' => number_format((float) $carta->total, 2, '.', ','),
            'fecha_generacion' => now()->format('d/m/Y'),
            'empresa_nombre' => $carta->empresa->nombre ?? '',
        ];
    }
}
