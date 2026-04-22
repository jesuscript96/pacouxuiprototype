<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CandidatoReclutamiento extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'candidatos_reclutamiento';

    public const ESTATUS_SIN_ATENDER = 'Sin atender';

    public const ESTATUS_EN_PROCESO = 'En proceso';

    public const ESTATUS_CONTRATADO = 'Contratado';

    public const ESTATUS_RECHAZADO = 'Rechazado';

    public const ESTATUS_NO_SE_PRESENTO = 'No se presentó';

    /** @var array<string, string> */
    public const ESTATUS_COLORES = [
        self::ESTATUS_SIN_ATENDER => 'gray',
        self::ESTATUS_EN_PROCESO => 'warning',
        self::ESTATUS_CONTRATADO => 'success',
        self::ESTATUS_RECHAZADO => 'danger',
        self::ESTATUS_NO_SE_PRESENTO => 'info',
    ];

    protected $fillable = [
        'vacante_id',
        'estatus',
        'valores_formulario',
        'archivos',
        'curp',
        'nombre_completo',
        'email',
        'telefono',
        'evaluacion_cv',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valores_formulario' => 'array',
            'archivos' => 'array',
            'evaluacion_cv' => 'decimal:1',
        ];
    }

    // === Relaciones ===

    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class);
    }

    public function historialEstatus(): HasMany
    {
        return $this->hasMany(HistorialEstatusCandidato::class, 'candidato_id')
            ->orderByDesc('fecha_inicio');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(MensajeCandidato::class, 'candidato_id')
            ->orderByDesc('created_at');
    }

    public function historialLaboral(): HasOne
    {
        return $this->hasOne(HistorialLaboralCandidato::class, 'candidato_id');
    }

    // === Métodos ===

    public function estatusActual(): ?HistorialEstatusCandidato
    {
        return $this->historialEstatus()->whereNull('fecha_fin')->first();
    }

    /**
     * BL: RN-05 — No se puede repetir un estatus ya registrado en el historial.
     */
    public function tieneEstatus(string $estatus): bool
    {
        return $this->historialEstatus()->where('estatus', $estatus)->exists();
    }

    public function colorEstatus(): string
    {
        return self::ESTATUS_COLORES[$this->estatus] ?? 'gray';
    }

    /**
     * @return list<string>
     */
    public static function estatusDisponibles(): array
    {
        return array_keys(self::ESTATUS_COLORES);
    }
}
