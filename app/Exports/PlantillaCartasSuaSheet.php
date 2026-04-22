<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PlantillaCartasSuaSheet implements FromArray, WithHeadings, WithTitle
{
    public function headings(): array
    {
        return [
            'Número de empleado',
            'Razón social',
            'RFC',
            'CURP',
            'Nombre',
            'Retiro',
            'C.V.',
            'Infonavit',
            'Tot RCV_INF',
            'Bimestre',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function array(): array
    {
        return [
            [
                '1234',
                'Mi Empresa S.A. de C.V.',
                'MELM850101XXX',
                'MELM850101HDFRRL09',
                'Juan Pérez López',
                '1500.00',
                '3000.00',
                '1200.00',
                '5700.00',
                'Enero-Febrero 2024',
            ],
        ];
    }

    public function title(): string
    {
        return 'Cartas SUA';
    }
}
