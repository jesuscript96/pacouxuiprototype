<?php

namespace App\Exports;

use App\Models\Empresa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class InstruccionesColaboradoresSheet implements FromCollection, WithTitle
{
    public function __construct(
        protected Empresa $empresa
    ) {}

    public function collection(): Collection
    {
        $periodicidades = 'SEMANAL, CATORCENAL, QUINCENAL, MENSUAL';
        $generos = 'M, F, OTRO';
        $instrucciones = [
            ['Instrucciones para carga masiva de colaboradores'],
            [],
            ['Columnas obligatorias: nombre, apellido_paterno, apellido_materno, fecha_nacimiento, fecha_ingreso, periodicidad_pago.'],
            ['Debe proporcionar al menos uno: email o telefono_movil (10 dígitos).'],
            [],
            ['Valores permitidos:'],
            ['periodicidad_pago', $periodicidades],
            ['genero', $generos],
            [],
            ['Fechas en formato Y-m-d (ej: 2024-01-15).'],
            [],
        ];

        $departamentos = $this->empresa->departamentos()->pluck('nombre', 'id');
        if ($departamentos->isNotEmpty()) {
            $instrucciones[] = ['Catálogo departamentos (ID => nombre):'];
            foreach ($departamentos as $id => $nombre) {
                $instrucciones[] = [$id, $nombre];
            }
            $instrucciones[] = [];
        }

        return collect($instrucciones);
    }

    public function title(): string
    {
        return 'Instrucciones';
    }
}
