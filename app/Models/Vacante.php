<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Vacante extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'vacantes';

    protected $fillable = [
        'empresa_id',
        'creado_por',
        'puesto',
        'requisitos',
        'aptitudes',
        'prestaciones',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Vacante $vacante): void {
            $vacante->slug = $vacante->generarSlug();
        });

        static::updating(function (Vacante $vacante): void {
            if ($vacante->isDirty('puesto')) {
                $vacante->slug = $vacante->generarSlug();
            }
        });

        static::deleting(function (Vacante $vacante): void {
            if ($vacante->tieneRegistrosAsociados()) {
                throw ValidationException::withMessages([
                    'vacante' => 'No se puede eliminar porque tiene candidatos asociados.',
                ]);
            }
        });
    }

    // === Relaciones ===

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function camposFormulario(): HasMany
    {
        return $this->hasMany(CampoFormularioVacante::class)->orderBy('orden');
    }

    public function candidatos(): HasMany
    {
        return $this->hasMany(CandidatoReclutamiento::class);
    }

    // === Métodos ===

    public function generarSlug(): string
    {
        $baseSlug = Str::slug($this->puesto);
        $slug = $baseSlug;
        $contador = 1;

        while (
            static::query()
                ->where('empresa_id', $this->empresa_id)
                ->where('slug', $slug)
                ->where('id', '!=', $this->id ?? 0)
                ->withTrashed()
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$contador}";
            $contador++;
        }

        return $slug;
    }

    public function urlPublica(): string
    {
        return route('postulacion.formulario', [$this->empresa_id, $this->slug]);
    }

    public function tieneRegistrosAsociados(): bool
    {
        return $this->candidatos()->withTrashed()->exists();
    }
}
