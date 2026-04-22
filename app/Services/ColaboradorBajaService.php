<?php

namespace App\Services;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ColaboradorBajaService
{
    /**
     * Registra una baja (programada o inmediata según la fecha, sin incluir el día siguiente como “futuro” en la misma TZ de app).
     */
    public function registrarBaja(Colaborador $colaborador, array $data): BajaColaborador
    {
        return DB::transaction(function () use ($colaborador, $data) {
            $this->validarDatosBaja($data);
            $this->validarPuedeRegistrarBaja($colaborador, $data);

            $fechaBaja = Carbon::parse($data['fecha_baja'])->startOfDay();
            $esInmediata = ! $fechaBaja->isFuture();

            $baja = $this->crearRegistroBaja($colaborador, $data, $fechaBaja);

            if ($esInmediata) {
                $this->ejecutarBaja($baja);
            }

            return $baja->fresh();
        });
    }

    /**
     * Ejecuta una baja programada (cron o transición desde edición).
     */
    public function ejecutarBaja(BajaColaborador $baja): void
    {
        DB::transaction(function () use ($baja): void {
            $bloqueada = BajaColaborador::query()
                ->whereKey($baja->id)
                ->lockForUpdate()
                ->first();

            if ($bloqueada === null || ! $bloqueada->esProgramada()) {
                return;
            }

            $this->aplicarEfectosBajaEjecutada($bloqueada);

            $bloqueada->update([
                'estado' => BajaColaborador::ESTADO_EJECUTADA,
                'ejecutada_at' => now(),
            ]);
        });
    }

    public function cancelarBaja(BajaColaborador $baja): void
    {
        if (! $baja->esProgramada()) {
            throw ValidationException::withMessages([
                'baja' => ['Solo se pueden cancelar bajas programadas.'],
            ]);
        }

        $baja->update([
            'estado' => BajaColaborador::ESTADO_CANCELADA,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizarBaja(BajaColaborador $baja, array $data): BajaColaborador
    {
        if (! $baja->esProgramada()) {
            throw ValidationException::withMessages([
                'baja' => ['Solo se pueden editar bajas programadas.'],
            ]);
        }

        $this->validarDatosBaja($data);

        return DB::transaction(function () use ($baja, $data) {
            $fechaBaja = Carbon::parse($data['fecha_baja'])->startOfDay();
            $this->validarFechaVsIngreso($baja->colaborador, $fechaBaja);

            $baja->update([
                'fecha_baja' => $fechaBaja->toDateString(),
                'motivo' => $data['motivo'],
                'comentarios' => $data['comentarios'] ?? null,
            ]);

            $baja->refresh();

            if (! $fechaBaja->isFuture()) {
                $this->ejecutarBaja($baja);
            }

            return $baja->fresh();
        });
    }

    public function procesarBajasProgramadasVencidas(): int
    {
        $ids = BajaColaborador::query()->vencidas()->pluck('id');
        $procesadas = 0;

        foreach ($ids as $id) {
            $baja = BajaColaborador::query()->find($id);
            if ($baja !== null) {
                $this->ejecutarBaja($baja);
                $procesadas++;
            }
        }

        return $procesadas;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validarDatosBaja(array $data): void
    {
        if (empty($data['fecha_baja'])) {
            throw ValidationException::withMessages([
                'fecha_baja' => ['La fecha de baja es obligatoria.'],
            ]);
        }

        $motivo = $data['motivo'] ?? null;
        if ($motivo === null || $motivo === '' || ! array_key_exists($motivo, BajaColaborador::motivosDisponibles())) {
            throw ValidationException::withMessages([
                'motivo' => ['El motivo de baja no es válido.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function validarPuedeRegistrarBaja(Colaborador $colaborador, array $data): void
    {
        if ($colaborador->tieneBajaProgramada()) {
            throw ValidationException::withMessages([
                'colaborador' => ['Este colaborador ya tiene una baja programada. Cancélala o edítala.'],
            ]);
        }

        if ($colaborador->bajas()->where('estado', BajaColaborador::ESTADO_EJECUTADA)->exists()) {
            throw ValidationException::withMessages([
                'colaborador' => ['Este colaborador ya tiene una baja ejecutada.'],
            ]);
        }

        $fechaBaja = Carbon::parse($data['fecha_baja'])->startOfDay();
        $this->validarFechaVsIngreso($colaborador, $fechaBaja);
    }

    private function validarFechaVsIngreso(Colaborador $colaborador, Carbon $fechaBaja): void
    {
        $fechaIngreso = $colaborador->fecha_ingreso;
        if ($fechaIngreso === null) {
            return;
        }

        $fi = $fechaIngreso->copy()->startOfDay();
        if ($fechaBaja->lte($fi)) {
            throw ValidationException::withMessages([
                'fecha_baja' => ['La fecha de baja debe ser posterior a la fecha de ingreso.'],
            ]);
        }
    }

    private function crearRegistroBaja(Colaborador $colaborador, array $data, Carbon $fechaBaja): BajaColaborador
    {
        $colaborador->loadMissing('user');

        return BajaColaborador::query()->create([
            'colaborador_id' => $colaborador->id,
            'user_id' => $colaborador->user?->id,
            'empresa_id' => $colaborador->empresa_id,
            'fecha_baja' => $fechaBaja->toDateString(),
            'motivo' => $data['motivo'],
            'comentarios' => $data['comentarios'] ?? null,
            'estado' => BajaColaborador::ESTADO_PROGRAMADA,
            'ubicacion_id' => $colaborador->ubicacion_id,
            'departamento_id' => $colaborador->departamento_id,
            'area_id' => $colaborador->area_id,
            'puesto_id' => $colaborador->puesto_id,
            'region_id' => $colaborador->region_id,
            'centro_pago_id' => $colaborador->centro_pago_id,
            'razon_social_id' => $colaborador->razon_social_id,
            'registrado_por' => auth()->id(),
        ]);
    }

    /**
     * BL: Baja ejecutada = cuenta y ficha fuera del listado operativo (soft delete en ambos).
     *
     * Pendiente cuando existan módulos en core (no hay equivalente a account_states del legacy aún):
     * - Cerrar periodo de cuenta / estado financiero del colaborador.
     * - Cuentas por cobrar o saldos pendientes.
     * - Cancelación de membresías / descuentos (jobs tipo legacy).
     * - Encuesta de salida.
     */
    private function aplicarEfectosBajaEjecutada(BajaColaborador $baja): void
    {
        $baja->loadMissing(['colaborador.user']);
        $colaborador = $baja->colaborador;
        if ($colaborador === null) {
            return;
        }

        $user = $colaborador->user;
        if ($user !== null && ! $user->trashed()) {
            $user->delete();
        }

        if (! $colaborador->trashed()) {
            $colaborador->delete();
        }
    }
}
