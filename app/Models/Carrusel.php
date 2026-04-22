<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Carrusel extends Model
{
    use HasFactory;
    use LogsModelActivity;

    protected $table = 'carruseles';

    protected $fillable = [
        'empresa_id',
        'nombre_archivo',
        'ruta',
        'orden',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
