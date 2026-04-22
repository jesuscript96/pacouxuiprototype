<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class CentroPago extends Model
{
    use LogsModelActivity;

    protected $table = 'centros_pagos';

    protected $fillable = ['nombre', 'registro_patronal', 'direccion_imss', 'empresa_id'];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function colaboradores(): HasMany
    {
        return $this->hasMany(Colaborador::class, 'centro_pago_id');
    }

    public function tieneColaboradoresAsociados(): bool
    {
        return $this->colaboradores()->withTrashed()->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (self $centroPago): void {
            if ($centroPago->tieneColaboradoresAsociados()) {
                throw ValidationException::withMessages([
                    'centro_pago' => 'No se puede eliminar porque tiene colaboradores asociados.',
                ]);
            }
        });
    }
}
