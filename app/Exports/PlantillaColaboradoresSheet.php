<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PlantillaColaboradoresSheet implements FromArray, WithHeadings, WithTitle
{
    public function headings(): array
    {
        return [
            'user_id',
            'name',
            'apellido_paterno',
            'apellido_materno',
            'email',
            'telefono_movil',
            'numero_colaborador',
            'fecha_nacimiento',
            'genero',
            'curp',
            'rfc',
            'nss',
            'fecha_ingreso',
            'periodicidad_pago',
            'salario_bruto',
            'salario_neto',
            'monto_maximo',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [],
        ];
    }

    public function title(): string
    {
        return 'Colaboradores';
    }
}
