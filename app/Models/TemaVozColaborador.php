<?php

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class TemaVozColaborador extends Model
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $table = 'temas_voz_colaboradores';

    protected $fillable = ['nombre', 'descripcion', 'exclusivo_para_empresa'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'exclusivo_para_empresa' => 'integer',
        ];
    }

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresas_temas_voz_colaboradores', 'tema_voz_colaborador_id', 'empresa_id');
    }

    /**
     * BL: No eliminar el tema mientras exista vínculo en la pivote con empresas (mismo criterio que CentroCosto).
     */
    public function tieneEmpresasAsignadas(): bool
    {
        return $this->empresas()->exists();
    }

    public function empresaExclusiva(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'exclusivo_para_empresa');
    }

    public function usuarios(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_temas_voz_colaboradores', 'tema_voz_colaborador_id', 'usuario_id');
    }

    /**
     * Sincroniza la tabla pivote con empresas según el valor de exclusivo_para_empresa:
     * - Si tiene empresa: solo esa empresa en la pivote.
     * - Si está vacío: todas las empresas en la pivote.
     */
    public function syncEmpresasSegunExclusivo(): void
    {
        $ids = $this->exclusivo_para_empresa !== null
            ? [$this->exclusivo_para_empresa]
            : Empresa::query()->orderBy('nombre')->pluck('id')->all();

        $this->empresas()->sync($ids);
    }

    protected static function booted(): void
    {
        static::deleting(function (TemaVozColaborador $tema): void {
            if ($tema->tieneEmpresasAsignadas()) {
                throw ValidationException::withMessages([
                    'tema_voz_colaborador' => 'No se puede eliminar el tema mientras esté vinculado a una empresa.',
                ]);
            }
        });
    }
}
