<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Database\Factories\ReingresoColaboradorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @use HasFactory<ReingresoColaboradorFactory>
 */
class ReingresoColaborador extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $table = 'reingresos_colaboradores';

    protected $fillable = [
        'baja_colaborador_id',
        'colaborador_anterior_id',
        'colaborador_nuevo_id',
        'user_anterior_id',
        'user_nuevo_id',
        'empresa_id',
        'fecha_ingreso_anterior',
        'fecha_ingreso_nuevo',
        'motivo_reingreso',
        'comentarios',
        'registrado_por',
    ];

    protected static function newFactory(): ReingresoColaboradorFactory
    {
        return ReingresoColaboradorFactory::new();
    }

    protected function casts(): array
    {
        return [
            'fecha_ingreso_anterior' => 'date',
            'fecha_ingreso_nuevo' => 'date',
        ];
    }

    public function bajaColaborador(): BelongsTo
    {
        return $this->belongsTo(BajaColaborador::class);
    }

    public function colaboradorAnterior(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_anterior_id')->withTrashed();
    }

    public function colaboradorNuevo(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_nuevo_id');
    }

    public function userAnterior(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_anterior_id')->withTrashed();
    }

    public function userNuevo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_nuevo_id');
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
