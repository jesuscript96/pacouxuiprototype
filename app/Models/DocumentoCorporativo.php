<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $user_id
 * @property int $carpeta_id
 * @property string|null $subcarpeta
 * @property string $nombre_documento
 */

/**
 * @use HasFactory<\Database\Factories\DocumentoCorporativoFactory>
 */
class DocumentoCorporativo extends Model
{
    use HasFactory;

    protected $table = 'documentos_corporativos';

    protected $fillable = [
        'user_id',
        'carpeta_id',
        'subcarpeta',
        'nombre_documento',
        'fecha_carga',
        'primera_visualizacion',
        'ultima_visualizacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_carga' => 'datetime',
            'primera_visualizacion' => 'datetime',
            'ultima_visualizacion' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'carpeta_id');
    }
}
