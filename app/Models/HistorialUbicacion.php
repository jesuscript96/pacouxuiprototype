<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialUbicacion extends Model
{
    protected $table = 'historial_ubicaciones';

    protected $fillable = ['user_id', 'colaborador_id', 'ubicacion_id', 'fecha_inicio', 'fecha_fin'];

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

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }
}
