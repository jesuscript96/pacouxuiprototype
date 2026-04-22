<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiarioColaborador extends Model
{
    use HasFactory;

    protected $table = 'beneficiarios_colaborador';

    protected $fillable = ['user_id', 'colaborador_id', 'nombre_completo', 'parentesco', 'porcentaje'];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }
}
