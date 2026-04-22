<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use App\Services\ArchivoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmpresaDocumento extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'empresas_documentos';

    protected $fillable = [
        'empresa_id',
        'ruta',
        'subido_por',
    ];

    protected function casts(): array
    {
        return [
            'empresa_id' => 'integer',
            'subido_por' => 'integer',
        ];
    }

    /**
     * Empresa a la que pertenece este documento.
     *
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    /**
     * Usuario que subió el documento.
     *
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subido_por');
    }

    /**
     * Genera la URL firmada (S3) o asset (local) del documento.
     */
    public function url(): string
    {
        if (blank($this->ruta)) {
            return '';
        }

        return app(ArchivoService::class)->url($this->ruta);
    }
}
