<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartaSua;
use App\Services\ArchivoService;
use App\Services\Nubarium\NubariumFirmaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CartaSuaController extends Controller
{
    public function __construct(
        private NubariumFirmaService $nubariumService,
    ) {}

    /**
     * GET /api/cartas-sua
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $colaborador = $user?->colaborador;

        if (! $colaborador) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene colaborador asociado',
            ], 403);
        }

        $query = CartaSua::query()
            ->where('colaborador_id', $colaborador->id)
            ->orderByDesc('created_at');

        if ($request->filled('bimestre')) {
            $query->where('bimestre', $request->bimestre);
        }

        if ($request->filled('estado')) {
            match ($request->estado) {
                'pendiente' => $query->whereNull('primera_visualizacion')->where('firmado', false),
                'vista' => $query->whereNotNull('primera_visualizacion')->where('firmado', false),
                'firmada' => $query->where('firmado', true),
                default => null,
            };
        }

        $cartas = $query->paginate((int) $request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $cartas->through(fn (CartaSua $c): array => $this->transformarCarta($c))->items(),
            'meta' => [
                'current_page' => $cartas->currentPage(),
                'last_page' => $cartas->lastPage(),
                'per_page' => $cartas->perPage(),
                'total' => $cartas->total(),
            ],
        ]);
    }

    /**
     * GET /api/cartas-sua/resumen
     */
    public function resumen(Request $request): JsonResponse
    {
        $user = $request->user();
        $colaborador = $user?->colaborador;

        if (! $colaborador) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene colaborador asociado',
            ], 403);
        }

        $base = CartaSua::query()->where('colaborador_id', $colaborador->id);

        return response()->json([
            'success' => true,
            'data' => [
                'total' => (clone $base)->count(),
                'pendientes' => (clone $base)->whereNull('primera_visualizacion')->where('firmado', false)->count(),
                'vistas' => (clone $base)->whereNotNull('primera_visualizacion')->where('firmado', false)->count(),
                'firmadas' => (clone $base)->where('firmado', true)->count(),
            ],
        ]);
    }

    /**
     * GET /api/cartas-sua/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $carta = $this->obtenerCartaDelUsuario($request, $id);

        if ($carta instanceof JsonResponse) {
            return $carta;
        }

        return response()->json([
            'success' => true,
            'data' => $this->transformarCarta($carta, detalle: true),
        ]);
    }

    /**
     * POST /api/cartas-sua/{id}/visualizar
     */
    public function registrarVisualizacion(Request $request, int $id): JsonResponse
    {
        $carta = $this->obtenerCartaDelUsuario($request, $id);

        if ($carta instanceof JsonResponse) {
            return $carta;
        }

        $carta->registrarVisualizacion();

        Log::info('CartaSua API: Visualización registrada', [
            'carta_id' => $carta->id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Visualización registrada',
            'data' => [
                'primera_visualizacion' => $carta->primera_visualizacion->toIso8601String(),
                'ultima_visualizacion' => $carta->ultima_visualizacion->toIso8601String(),
                'estado' => $carta->estado,
                'estado_label' => $carta->estado_label,
            ],
        ]);
    }

    /**
     * POST /api/cartas-sua/{id}/firmar
     */
    public function firmar(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'imagen_firma' => 'required|string',
        ]);

        $carta = $this->obtenerCartaDelUsuario($request, $id);

        if ($carta instanceof JsonResponse) {
            return $carta;
        }

        if ($carta->firmado) {
            return response()->json([
                'success' => false,
                'message' => 'La carta ya está firmada',
            ], 422);
        }

        $resultado = $this->nubariumService->firmarCartaSua($carta, $request->imagen_firma);

        if (! $resultado['success']) {
            return response()->json([
                'success' => false,
                'message' => $resultado['error'] ?? 'Error al procesar firma',
            ], 500);
        }

        // BL: Si Nubarium devolvió PDF firmado, actualizarlo en storage
        if (! empty($resultado['pdf_firmado']) && $carta->pdf_path) {
            $this->reemplazarPdfConFirmado($carta, $resultado['pdf_firmado']);
        }

        $carta->marcarComoFirmada(
            imagenFirma: $request->imagen_firma,
            nom151: $resultado['nom151'],
            hashNom151: $resultado['hash'],
            codigoValidacion: $resultado['codigo_validacion'],
        );

        Log::info('CartaSua API: Carta firmada', [
            'carta_id' => $carta->id,
            'user_id' => $request->user()->id,
            'con_nubarium' => ! empty($resultado['nom151']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Carta firmada exitosamente',
            'data' => [
                'firmado' => true,
                'fecha_firma' => $carta->fecha_firma->toIso8601String(),
                'tiene_nom151' => ! empty($carta->nom151),
                'codigo_validacion' => $carta->codigo_validacion,
            ],
        ]);
    }

    /**
     * GET /api/cartas-sua/{id}/pdf
     */
    public function descargarPdf(Request $request, int $id): JsonResponse|StreamedResponse
    {
        $carta = $this->obtenerCartaDelUsuario($request, $id);

        if ($carta instanceof JsonResponse) {
            return $carta;
        }

        if (! $carta->pdf_path) {
            return response()->json([
                'success' => false,
                'message' => 'PDF no disponible',
            ], 404);
        }

        try {
            $archivoService = app(ArchivoService::class);

            return $archivoService->descargar(
                $carta->pdf_path,
                "carta-sua-{$carta->bimestre}.pdf",
            );
        } catch (\Exception $e) {
            Log::error('CartaSua API: Error al descargar PDF', [
                'carta_id' => $carta->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al descargar PDF',
            ], 500);
        }
    }

    // =========================================================================
    // Helpers privados
    // =========================================================================

    /**
     * Obtiene una carta que pertenezca al colaborador del usuario autenticado.
     * Retorna JsonResponse de error si no se puede acceder.
     */
    private function obtenerCartaDelUsuario(Request $request, int $id): CartaSua|JsonResponse
    {
        $user = $request->user();
        $colaborador = $user?->colaborador;

        if (! $colaborador) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no tiene colaborador asociado',
            ], 403);
        }

        $carta = CartaSua::query()
            ->where('id', $id)
            ->where('colaborador_id', $colaborador->id)
            ->first();

        if (! $carta) {
            return response()->json([
                'success' => false,
                'message' => 'Carta no encontrada',
            ], 404);
        }

        return $carta;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformarCarta(CartaSua $carta, bool $detalle = false): array
    {
        $data = [
            'id' => $carta->id,
            'bimestre' => $carta->bimestre,
            'razon_social' => $carta->razon_social,
            'total' => $carta->total,
            'total_formateado' => $carta->total_formateado,
            'estado' => $carta->estado,
            'estado_label' => $carta->estado_label,
            'firmado' => $carta->firmado,
            'fecha_firma' => $carta->fecha_firma?->toIso8601String(),
            'created_at' => $carta->created_at->toIso8601String(),
        ];

        if ($detalle) {
            $data += [
                'retiro' => $carta->retiro,
                'cesantia_vejez' => $carta->cesantia_vejez,
                'infonavit' => $carta->infonavit,
                'primera_visualizacion' => $carta->primera_visualizacion?->toIso8601String(),
                'ultima_visualizacion' => $carta->ultima_visualizacion?->toIso8601String(),
                'tiene_nom151' => ! empty($carta->nom151),
                'codigo_validacion' => $carta->codigo_validacion,
                'pdf_disponible' => ! empty($carta->pdf_path),
            ];
        }

        return $data;
    }

    /**
     * BL: Legacy reemplaza el PDF original con el firmado por Nubarium.
     */
    private function reemplazarPdfConFirmado(CartaSua $carta, string $pdfFirmadoBase64): void
    {
        try {
            $archivoService = app(ArchivoService::class);
            $contenido = base64_decode($pdfFirmadoBase64);
            $archivoService->disco()->put($carta->pdf_path, $contenido);
        } catch (\Exception $e) {
            Log::warning('CartaSua API: No se pudo reemplazar PDF con versión firmada', [
                'carta_id' => $carta->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
