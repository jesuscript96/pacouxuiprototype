<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampoFormularioVacante extends Model
{
    use HasFactory;

    protected $table = 'campos_formulario_vacante';

    public const TIPOS = [
        'text' => 'Texto',
        'textarea' => 'Texto largo',
        'number' => 'Número',
        'email' => 'Correo electrónico',
        'phone' => 'Teléfono',
        'date' => 'Fecha',
        'select' => 'Selección',
        'file' => 'Archivo',
    ];

    protected $fillable = [
        'vacante_id',
        'tipo',
        'etiqueta',
        'nombre',
        'requerido',
        'placeholder',
        'tipos_archivo',
        'longitud_minima',
        'longitud_maxima',
        'opciones',
        'es_dependiente',
        'campo_padre',
        'valor_activador',
        'orden',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requerido' => 'boolean',
            'opciones' => 'array',
            'es_dependiente' => 'boolean',
            'longitud_minima' => 'integer',
            'longitud_maxima' => 'integer',
            'orden' => 'integer',
        ];
    }

    public function vacante(): BelongsTo
    {
        return $this->belongsTo(Vacante::class);
    }

    public function esTipoArchivo(): bool
    {
        return $this->tipo === 'file';
    }
}
