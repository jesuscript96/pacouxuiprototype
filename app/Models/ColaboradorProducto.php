<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColaboradorProducto extends Model
{
    protected $table = 'colaborador_producto';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'user_id',
        'colaborador_id',
        'producto_id',
        'estado',
        'razon',
        'tipo_cambio',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
