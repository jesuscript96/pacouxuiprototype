<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MensajeCandidato extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'mensajes_candidato';

    protected $fillable = [
        'candidato_id',
        'user_id',
        'comentario',
    ];

    // === Relaciones ===

    public function candidato(): BelongsTo
    {
        return $this->belongsTo(CandidatoReclutamiento::class, 'candidato_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
