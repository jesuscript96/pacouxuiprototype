<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ValidadorDocumentoInterface;
use App\Models\CandidatoReclutamiento;
use App\Services\ValidacionDocumental\CsfValidadorService;
use App\Services\ValidacionDocumental\CvAnalizadorService;
use App\Services\ValidacionDocumental\DomicilioValidadorService;
use App\Services\ValidacionDocumental\IneValidadorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ValidarDocumentoCandidatoJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public int $timeout = 120;

    public function __construct(
        public CandidatoReclutamiento $candidato,
        public string $nombreCampo,
        public string $tipoDocumento,
    ) {}

    public function handle(): void
    {
        $archivos = $this->candidato->archivos ?? [];

        if (! isset($archivos[$this->nombreCampo]['path'])) {
            Log::warning('ValidarDocumentoCandidatoJob: Archivo no encontrado', [
                'candidato_id' => $this->candidato->id,
                'campo' => $this->nombreCampo,
            ]);

            return;
        }

        $rutaArchivo = $archivos[$this->nombreCampo]['path'];

        $validador = $this->obtenerValidador($this->tipoDocumento);

        if (! $validador) {
            Log::warning('ValidarDocumentoCandidatoJob: Tipo de documento no soportado', [
                'tipo' => $this->tipoDocumento,
            ]);

            return;
        }

        $resultado = $validador->validar($this->candidato, $this->nombreCampo, $rutaArchivo);

        $archivos[$this->nombreCampo] = array_merge(
            $archivos[$this->nombreCampo],
            $resultado->toArray(),
        );

        $this->candidato->update(['archivos' => $archivos]);

        if ($this->tipoDocumento === 'cv' && $resultado->score !== null) {
            $this->candidato->update(['evaluacion_cv' => $resultado->score]);
        }

        Log::info('ValidarDocumentoCandidatoJob: Validación completada', [
            'candidato_id' => $this->candidato->id,
            'campo' => $this->nombreCampo,
            'tipo' => $this->tipoDocumento,
            'is_valid' => $resultado->isValid,
        ]);
    }

    private function obtenerValidador(string $tipo): ?ValidadorDocumentoInterface
    {
        return match ($tipo) {
            'ine', 'ine_frente', 'ine_reverso' => app(IneValidadorService::class),
            'csf', 'constancia_fiscal' => app(CsfValidadorService::class),
            'domicilio', 'comprobante_domicilio' => app(DomicilioValidadorService::class),
            'cv', 'curriculum' => app(CvAnalizadorService::class),
            default => null,
        };
    }
}
