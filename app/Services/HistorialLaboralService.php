<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CandidatoReclutamiento;
use App\Models\HistorialLaboralCandidato;
use App\Services\Palenca\PalencaService;
use Illuminate\Support\Facades\Log;

/**
 * Servicio de Historial Laboral IMSS vía Palenca (Buró de Ingresos).
 *
 * Legacy: app/Http/Controllers/Admin/EmployeeHistoryController.php
 * Legacy: app/Jobs/EmployeeHistories/EmployeeHistoryJob.php
 */
class HistorialLaboralService
{
    public function __construct(
        private PalencaService $palenca,
    ) {}

    /**
     * Iniciar proceso de consulta de historial laboral.
     */
    public function iniciarConsulta(CandidatoReclutamiento $candidato): ?HistorialLaboralCandidato
    {
        $curp = $candidato->curp;

        if (empty($curp) || strlen($curp) !== 18) {
            Log::warning('HistorialLaboralService: CURP inválido', [
                'candidato_id' => $candidato->id,
                'curp' => $curp,
            ]);

            return null;
        }

        $existente = HistorialLaboralCandidato::query()
            ->where('candidato_id', $candidato->id)
            ->first();

        if ($existente) {
            return $existente;
        }

        if (! $this->palenca->estaConfigurado()) {
            Log::info('HistorialLaboralService: Palenca no configurado');

            return HistorialLaboralCandidato::create([
                'candidato_id' => $candidato->id,
                'curp' => $curp,
                'account_status' => HistorialLaboralCandidato::STATUS_PENDING,
                'failed_reason' => 'Servicio Palenca no configurado',
            ]);
        }

        // BL: Paso 1 — Crear consent con CURP
        $consent = $this->palenca->crearConsent($curp, [
            'candidato_id' => $candidato->id,
            'empresa_id' => $candidato->vacante?->empresa_id,
        ]);

        if (! $consent['success']) {
            Log::error('HistorialLaboralService: Error creando consent', [
                'candidato_id' => $candidato->id,
                'error' => $consent['error'] ?? 'desconocido',
            ]);

            return HistorialLaboralCandidato::create([
                'candidato_id' => $candidato->id,
                'curp' => $curp,
                'account_status' => HistorialLaboralCandidato::STATUS_FAILED,
                'failed_reason' => $consent['error'] ?? 'Error creando consent',
            ]);
        }

        $consentId = $consent['consent_id'];

        // BL: Paso 2 — Crear verificación (solo identifier/CURP, sin consentId)
        $verification = $this->palenca->crearVerification($curp);

        if (! $verification['success']) {
            Log::error('HistorialLaboralService: Error creando verificación', [
                'candidato_id' => $candidato->id,
                'consent_id' => $consentId,
                'error' => $verification['error'] ?? 'desconocido',
            ]);

            return HistorialLaboralCandidato::create([
                'candidato_id' => $candidato->id,
                'curp' => $curp,
                'consent_id' => $consentId,
                'account_status' => HistorialLaboralCandidato::STATUS_FAILED,
                'failed_reason' => $verification['error'] ?? 'Error creando verificación',
            ]);
        }

        $historial = HistorialLaboralCandidato::create([
            'candidato_id' => $candidato->id,
            'curp' => $curp,
            'consent_id' => $consentId,
            'verification_id' => $verification['verification_id'],
            'account_status' => HistorialLaboralCandidato::STATUS_PENDING,
        ]);

        Log::info('HistorialLaboralService: Verificación iniciada', [
            'candidato_id' => $candidato->id,
            'historial_id' => $historial->id,
            'verification_id' => $verification['verification_id'],
        ]);

        return $historial;
    }

    /**
     * BL: Procesa webhook de Palenca cuando la verificación está lista.
     * Legacy: busca por identifier + consent_id (= verification_id del webhook),
     * verifica data_available y entities, luego llama getProfile y getEmployment.
     *
     * @param  array<string, mixed>  $payload
     */
    public function procesarWebhook(string $verificationId, array $payload): bool
    {
        $identifier = $payload['identifier'] ?? null;

        // BL: Legacy busca por identifier + consent_id (donde consent_id = verification_id del webhook)
        $historial = HistorialLaboralCandidato::query()
            ->where('verification_id', $verificationId)
            ->first();

        if (! $historial) {
            Log::warning('HistorialLaboralService: Webhook para verification_id desconocido', [
                'verification_id' => $verificationId,
            ]);

            return false;
        }

        $curp = $identifier ?? $historial->curp;
        $dataAvailable = $payload['data_available'] ?? true;
        $entities = $payload['entities'] ?? ['profile', 'employment'];

        if (! $dataAvailable) {
            $historial->update([
                'account_status' => HistorialLaboralCandidato::STATUS_FAILED,
                'failed_reason' => 'data_not_available',
            ]);

            return false;
        }

        $historial->update(['account_status' => HistorialLaboralCandidato::STATUS_COMPLETED]);

        // BL: Legacy llama profile y employment por separado según entities
        if (in_array('profile', $entities)) {
            $perfil = $this->palenca->obtenerPerfil($curp);

            if ($perfil['success']) {
                $historial->update([
                    'nss' => $perfil['nss'],
                    'nombre_imss' => $perfil['nombre_imss'],
                    'estatus_laboral' => $perfil['estatus_laboral'],
                ]);
            }
        }

        if (in_array('employment', $entities)) {
            $empleos = $this->palenca->obtenerEmpleos($curp);

            if ($empleos['success']) {
                $historial->update([
                    'semanas_cotizadas' => $empleos['semanas_cotizadas'],
                    'empleos' => $empleos['empleos'],
                    'ultima_actualizacion' => now(),
                ]);
            }
        }

        Log::info('HistorialLaboralService: Verificación completada vía webhook', [
            'candidato_id' => $historial->candidato_id,
            'curp' => $curp,
            'entities' => $entities,
        ]);

        return true;
    }

    /**
     * Consulta el estado actual de un historial pendiente en Palenca.
     * BL: Legacy usa CURP (identifier) para consultar perfil y empleos.
     */
    public function consultarEstado(CandidatoReclutamiento $candidato): ?HistorialLaboralCandidato
    {
        $historial = $candidato->historialLaboral;

        if (! $historial || ! $historial->curp) {
            return $historial;
        }

        if ($historial->estaCompleto()) {
            return $historial;
        }

        $curp = $historial->curp;

        $perfil = $this->palenca->obtenerPerfil($curp);

        if ($perfil['success']) {
            $historial->update([
                'account_status' => HistorialLaboralCandidato::STATUS_COMPLETED,
                'nss' => $perfil['nss'],
                'nombre_imss' => $perfil['nombre_imss'],
                'estatus_laboral' => $perfil['estatus_laboral'],
            ]);

            $empleos = $this->palenca->obtenerEmpleos($curp);

            if ($empleos['success']) {
                $historial->update([
                    'semanas_cotizadas' => $empleos['semanas_cotizadas'],
                    'empleos' => $empleos['empleos'],
                    'ultima_actualizacion' => now(),
                ]);
            }

            $historial->refresh();
        }

        return $historial;
    }
}
