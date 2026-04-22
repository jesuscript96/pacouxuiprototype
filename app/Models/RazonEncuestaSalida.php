<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RazonEncuestaSalida extends Model
{
    protected $table = 'razones_encuesta_salida';

    protected $fillable = ['empresa_id', 'razon'];

    /**
     * BL: Catálogo fijo alineado al CheckboxList de encuesta de salida (Filament EmpresaForm).
     * La BD puede contener textos distintos por seeders legacy o migración; no son válidos para el formulario hasta normalizarlos.
     *
     * @return list<string>
     */
    public static function catalogoRazonesPermitidas(): array
    {
        return [
            'ABANDONO',
            'RENUNCIA',
            'DESPIDO',
            'FALLECIMIENTO',
            'TÉRMINO DE CONTRATO',
        ];
    }

    /**
     * @param  array<int, string>  $razones
     * @return list<string>
     */
    public static function soloRazonesDelCatalogo(array $razones): array
    {
        return array_values(array_intersect($razones, self::catalogoRazonesPermitidas()));
    }

    /**
     * @return array<string, string>
     */
    public static function opcionesCheckboxCatalogo(): array
    {
        $items = self::catalogoRazonesPermitidas();

        return array_combine($items, $items);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
