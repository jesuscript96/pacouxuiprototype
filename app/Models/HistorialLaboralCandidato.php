<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialLaboralCandidato extends Model
{
    use HasFactory;

    protected $table = 'historial_laboral_candidato';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_FAILED = 'FAILED';

    protected $fillable = [
        'candidato_id',
        'curp',
        'consent_id',
        'verification_id',
        'account_status',
        'failed_reason',
        'nss',
        'nombre_imss',
        'semanas_cotizadas',
        'estatus_laboral',
        'empresa_actual',
        'empleos',
        'ultima_actualizacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'empleos' => 'array',
            'semanas_cotizadas' => 'integer',
            'ultima_actualizacion' => 'datetime',
        ];
    }

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(CandidatoReclutamiento::class, 'candidato_id');
    }

    public function estaEmpleado(): bool
    {
        return $this->estatus_laboral === 'EMPLEADO';
    }

    public function estaPendiente(): bool
    {
        return $this->account_status === self::STATUS_PENDING;
    }

    public function estaCompleto(): bool
    {
        return $this->account_status === self::STATUS_COMPLETED;
    }

    public function fallo(): bool
    {
        return $this->account_status === self::STATUS_FAILED;
    }
}
