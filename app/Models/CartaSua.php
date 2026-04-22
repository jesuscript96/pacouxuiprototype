<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<\Database\Factories\CartaSuaFactory>
 */
class CartaSua extends Model
{
    use HasFactory;

    protected $table = 'cartas_sua';

    protected $fillable = [
        'empresa_id',
        'colaborador_id',
        'bimestre',
        'razon_social',
        'retiro',
        'cesantia_vejez',
        'infonavit',
        'total',
        'datos_origen',
        'pdf_path',
        'primera_visualizacion',
        'ultima_visualizacion',
        'firmado',
        'fecha_firma',
        'imagen_firma',
        'nom151',
        'hash_nom151',
        'codigo_validacion',
    ];

    protected function casts(): array
    {
        return [
            'retiro' => 'decimal:2',
            'cesantia_vejez' => 'decimal:2',
            'infonavit' => 'decimal:2',
            'total' => 'decimal:2',
            'datos_origen' => 'array',
            'primera_visualizacion' => 'datetime',
            'ultima_visualizacion' => 'datetime',
            'firmado' => 'boolean',
            'fecha_firma' => 'datetime',
        ];
    }

    public const ESTADO_PENDIENTE = 'pendiente';

    public const ESTADO_VISTA = 'vista';

    public const ESTADO_FIRMADA = 'firmada';

    // =========================================================================
    // Relaciones
    // =========================================================================

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    // =========================================================================
    // Accessors
    // =========================================================================

    /**
     * BL: Estado calculado — firmada > vista > pendiente.
     */
    public function getEstadoAttribute(): string
    {
        if ($this->firmado) {
            return self::ESTADO_FIRMADA;
        }

        if ($this->primera_visualizacion !== null) {
            return self::ESTADO_VISTA;
        }

        return self::ESTADO_PENDIENTE;
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_FIRMADA => 'success',
            self::ESTADO_VISTA => 'info',
            self::ESTADO_PENDIENTE => 'warning',
            default => 'gray',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            self::ESTADO_FIRMADA => 'Firmada',
            self::ESTADO_VISTA => 'Vista',
            self::ESTADO_PENDIENTE => 'Pendiente',
            default => 'Desconocido',
        };
    }

    public function getTotalFormateadoAttribute(): string
    {
        return '$'.number_format((float) $this->total, 2, '.', ',');
    }

    // =========================================================================
    // Métodos
    // =========================================================================

    /**
     * BL: RN-06 — Registra visualización desde app móvil.
     * Primera vez setea primera_visualizacion; siempre actualiza ultima_visualizacion.
     */
    public function registrarVisualizacion(): void
    {
        $ahora = now();

        if ($this->primera_visualizacion === null) {
            $this->primera_visualizacion = $ahora;
        }

        $this->ultima_visualizacion = $ahora;
        $this->save();
    }

    /**
     * BL: RN-08 — Marca la carta como firmada con datos opcionales de Nubarium.
     */
    public function marcarComoFirmada(
        ?string $imagenFirma = null,
        ?string $nom151 = null,
        ?string $hashNom151 = null,
        ?string $codigoValidacion = null,
    ): void {
        $this->update([
            'firmado' => true,
            'fecha_firma' => now(),
            'imagen_firma' => $imagenFirma,
            'nom151' => $nom151,
            'hash_nom151' => $hashNom151,
            'codigo_validacion' => $codigoValidacion,
        ]);
    }

    /**
     * BL: RN-01 — Verifica unicidad de (colaborador, bimestre, razón social).
     */
    public static function existeDuplicado(
        int $colaboradorId,
        string $bimestre,
        string $razonSocial,
    ): bool {
        return static::query()
            ->where('colaborador_id', $colaboradorId)
            ->where('bimestre', $bimestre)
            ->where('razon_social', $razonSocial)
            ->exists();
    }

    /**
     * BL: Retorna datos para generar el PDF. Prefiere datos_origen (del Excel)
     * y hace fallback a datos del colaborador si no existen.
     *
     * @return array<string, mixed>
     */
    public function getDatosParaPdf(): array
    {
        if ($this->datos_origen !== null) {
            return $this->datos_origen;
        }

        return [
            'nombre_empleado' => $this->colaborador->nombre_completo ?? '',
            'rfc' => $this->colaborador->rfc ?? '',
            'curp' => $this->colaborador->curp ?? '',
            'razon_social' => $this->razon_social,
            'bimestre' => $this->bimestre,
            'retiro' => $this->retiro,
            'cesantia_vejez' => $this->cesantia_vejez,
            'infonavit' => $this->infonavit,
            'total' => $this->total,
        ];
    }
}
