<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialPuesto extends Model
{
    protected $table = 'historial_puestos';

    protected $fillable = ['user_id', 'colaborador_id', 'puesto_id', 'fecha_inicio', 'fecha_fin'];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function puesto(): BelongsTo
    {
        return $this->belongsTo(Puesto::class);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }
}
