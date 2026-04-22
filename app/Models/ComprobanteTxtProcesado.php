<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComprobanteTxtProcesado extends Model
{
    protected $table = 'comprobantes_txt_procesados';

    protected $fillable = ['nombre'];

    public function intentosCobro(): HasMany
    {
        return $this->hasMany(IntentoCobro::class, 'comprobante_txt_procesado_id');
    }
}
