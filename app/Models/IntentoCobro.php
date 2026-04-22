<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntentoCobro extends Model
{
    protected $table = 'intentos_cobro';

    protected $fillable = [
        'codigo_razon',
        'referencia_numerica_emisor',
        'descripcion',
        'fecha_liquidacion',
        'cuenta_bancaria_id',
        'cuenta_por_cobrar_id',
        'monto',
        'comprobante_txt_procesado_id',
        'es_recargo',
        'estado_recargo',
        'monto_cobrado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_liquidacion' => 'datetime',
            'monto' => 'decimal:2',
            'monto_cobrado' => 'decimal:2',
            'es_recargo' => 'boolean',
        ];
    }

    public function cuentaBancaria(): BelongsTo
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_por_cobrar_id');
    }

    public function comprobanteTxtProcesado(): BelongsTo
    {
        return $this->belongsTo(ComprobanteTxtProcesado::class, 'comprobante_txt_procesado_id');
    }
}
