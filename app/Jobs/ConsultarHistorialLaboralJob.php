<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\CandidatoReclutamiento;
use App\Services\HistorialLaboralService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ConsultarHistorialLaboralJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public int $timeout = 60;

    public function __construct(
        public CandidatoReclutamiento $candidato,
    ) {}

    public function handle(HistorialLaboralService $service): void
    {
        Log::info('ConsultarHistorialLaboralJob: Iniciando consulta', [
            'candidato_id' => $this->candidato->id,
            'curp' => $this->candidato->curp,
        ]);

        $historial = $service->iniciarConsulta($this->candidato);

        if (! $historial) {
            Log::error('ConsultarHistorialLaboralJob: No se pudo iniciar consulta', [
                'candidato_id' => $this->candidato->id,
            ]);

            return;
        }

        Log::info('ConsultarHistorialLaboralJob: Consulta iniciada', [
            'candidato_id' => $this->candidato->id,
            'historial_id' => $historial->id,
            'status' => $historial->account_status,
        ]);
    }
}
