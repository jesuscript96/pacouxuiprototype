<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @use HasFactory<\Database\Factories\CarpetaFactory>
 */
class Carpeta extends Model
{
    use HasFactory;
    use LogsModelActivity;

    public const TIPO_DOCUMENTOS_CORPORATIVOS = 'documentos_corporativos';

    protected $table = 'carpetas';

    protected $fillable = [
        'nombre',
        'empresa_id',
        'url',
        'tipo',
        'usuario_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * @return HasMany<Subcarpeta, $this>
     */
    public function subcarpetas(): HasMany
    {
        return $this->hasMany(Subcarpeta::class, 'carpeta_id');
    }

    /**
     * @return HasMany<DocumentoCorporativo, $this>
     */
    public function documentosCorporativos(): HasMany
    {
        return $this->hasMany(DocumentoCorporativo::class, 'carpeta_id');
    }

    /**
     * @return BelongsToMany<Ubicacion, $this>
     */
    public function ubicaciones(): BelongsToMany
    {
        return $this->belongsToMany(Ubicacion::class, 'carpeta_ubicacion', 'carpeta_id', 'ubicacion_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Departamento, $this>
     */
    public function departamentos(): BelongsToMany
    {
        return $this->belongsToMany(Departamento::class, 'carpeta_departamento', 'carpeta_id', 'departamento_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Area, $this>
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'carpeta_area', 'carpeta_id', 'area_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Puesto, $this>
     */
    public function puestos(): BelongsToMany
    {
        return $this->belongsToMany(Puesto::class, 'carpeta_puesto', 'carpeta_id', 'puesto_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Empresa, $this>
     */
    public function empresasPivot(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'carpeta_empresa', 'carpeta_id', 'empresa_id')
            ->withTimestamps();
    }

    /**
     * BL: Solo bloquear borrado si algún documento corporativo ya registró visualización
     * (primera o última). Puede haber filas en documentos_corporativos sin fechas y aún así
     * permitirse eliminar la carpeta desde la UI.
     */
    public function tieneRegistrosAsociados(): bool
    {
        return $this->documentosCorporativos()
            ->where(function (Builder $consulta): void {
                $consulta
                    ->whereNotNull('primera_visualizacion')
                    ->orWhereNotNull('ultima_visualizacion');
            })
            ->exists();
    }

    protected static function booted(): void
    {
        static::deleting(function (Carpeta $carpeta): void {
            if ($carpeta->tieneRegistrosAsociados()) {
                throw new \RuntimeException(
                    'No se puede eliminar la carpeta porque al menos un documento ya fue visualizado por un colaborador.',
                );
            }

            $disk = Storage::disk('uploads');
            if ($carpeta->url !== '' && $disk->exists($carpeta->url)) {
                $disk->deleteDirectory($carpeta->url);
            }
        });
    }
}
