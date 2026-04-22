<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialRazonSocial extends Model
{
    protected $table = 'historial_razones_sociales';

    protected $fillable = ['user_id', 'colaborador_id', 'razon_social_id', 'fecha_inicio', 'fecha_fin'];

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

    public function razonSocial(): BelongsTo
    {
        return $this->belongsTo(Razonsocial::class, 'razon_social_id');
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->whereNull('fecha_fin');
    }
}
