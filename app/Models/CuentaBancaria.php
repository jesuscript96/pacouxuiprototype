<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EstadoVerificacionCuenta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaBancaria extends Model
{
    use SoftDeletes;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'numero',
        'tipo',
        'alias',
        'estado',
        'banco_id',
        'user_id',
        'colaborador_id',
        'es_nomina',
        'enviado_verificacion',
    ];

    protected $attributes = [
        'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR->value,
        'enviado_verificacion' => false,
    ];

    protected function casts(): array
    {
        return [
            'estado' => EstadoVerificacionCuenta::class,
            'es_nomina' => 'boolean',
            'enviado_verificacion' => 'boolean',
        ];
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(Banco::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class, 'cuenta_bancaria_id');
    }

    public function intentosCobro(): HasMany
    {
        return $this->hasMany(IntentoCobro::class, 'cuenta_bancaria_id');
    }

    public function adelantosNomina(): HasMany
    {
        return $this->hasMany(AdelantoNomina::class, 'cuenta_bancaria_id');
    }

    public function recargas(): HasMany
    {
        return $this->hasMany(Recarga::class, 'cuenta_bancaria_id');
    }

    public function serviciosPago(): HasMany
    {
        return $this->hasMany(ServicioPago::class, 'cuenta_bancaria_id');
    }

    public function puedeVerificarse(): bool
    {
        return ($this->estado ?? EstadoVerificacionCuenta::SIN_VERIFICAR)->puedeVerificarse();
    }

    public function puedeReenviarse(): bool
    {
        return ($this->estado ?? EstadoVerificacionCuenta::SIN_VERIFICAR)->puedeReenviarse();
    }

    public function marcarComoVerificada(): void
    {
        $this->update([
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
            'es_nomina' => true,
        ]);

        if ($this->colaborador_id !== null) {
            self::query()
                ->where('colaborador_id', $this->colaborador_id)
                ->whereKeyNot($this->id)
                ->update(['es_nomina' => false]);
        }
    }

    public function marcarComoRechazada(): void
    {
        $this->update([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);
    }

    public function reenviarAVerificacion(): void
    {
        $this->update([
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
            'enviado_verificacion' => false,
        ]);
    }

    public function marcarComoEnviada(): void
    {
        $this->update([
            'enviado_verificacion' => true,
        ]);
    }

    public function esCuentaTemporal(): bool
    {
        return $this->banco_id === 23;
    }

    public function scopeSinVerificar(Builder $query): Builder
    {
        return $query->where('estado', EstadoVerificacionCuenta::SIN_VERIFICAR->value);
    }

    public function scopeVerificadas(Builder $query): Builder
    {
        return $query->where('estado', EstadoVerificacionCuenta::VERIFICADA->value);
    }

    public function scopeRechazadas(Builder $query): Builder
    {
        return $query->where('estado', EstadoVerificacionCuenta::RECHAZADA->value);
    }

    public function scopePendientesDeEnvio(Builder $query): Builder
    {
        return $query->where('enviado_verificacion', false);
    }

    public function scopeEnviadas(Builder $query): Builder
    {
        return $query->where('enviado_verificacion', true);
    }

    public function scopeDeNomina(Builder $query): Builder
    {
        return $query->where('es_nomina', true);
    }
}
