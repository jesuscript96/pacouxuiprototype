<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstatusCandidato extends Model
{
    use HasFactory;

    protected $table = 'historial_estatus_candidato';

    protected $fillable = [
        'candidato_id',
        'estatus',
        'creado_por',
        'fecha_inicio',
        'fecha_fin',
        'duracion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    // === Relaciones ===

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(CandidatoReclutamiento::class, 'candidato_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // === Métodos ===

    /**
     * BL: RN-06 — Cierra el estatus actual del candidato poniendo fecha_fin y calculando duración.
     */
    public static function cerrarActual(CandidatoReclutamiento $candidato): void
    {
        $actual = $candidato->estatusActual();

        if (! $actual) {
            return;
        }

        $actual->update([
            'fecha_fin' => now(),
            'duracion' => self::calcularDuracion($actual->fecha_inicio, now()),
        ]);
    }

    public static function calcularDuracion(Carbon $inicio, Carbon $fin): string
    {
        $diff = $inicio->diff($fin);
        $partes = [];

        if ($diff->y > 0) {
            $partes[] = $diff->y.' '.($diff->y === 1 ? 'año' : 'años');
        }
        if ($diff->m > 0) {
            $partes[] = $diff->m.' '.($diff->m === 1 ? 'mes' : 'meses');
        }
        if ($diff->d > 0) {
            $partes[] = $diff->d.' '.($diff->d === 1 ? 'día' : 'días');
        }
        if (empty($partes) && $diff->h > 0) {
            $partes[] = $diff->h.' '.($diff->h === 1 ? 'hora' : 'horas');
        }
        if (empty($partes)) {
            $partes[] = 'menos de 1 hora';
        }

        return implode(' ', $partes);
    }
}
