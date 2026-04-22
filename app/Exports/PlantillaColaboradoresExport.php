<?php

namespace App\Exports;

use App\Models\Empresa;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantillaColaboradoresExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        protected Empresa $empresa
    ) {}

    /**
     * @return array<int, \Maatwebsite\Excel\Concerns\WithTitle|\Maatwebsite\Excel\Concerns\FromCollection>
     */
    public function sheets(): array
    {
        return [
            new PlantillaColaboradoresSheet,
            new InstruccionesColaboradoresSheet($this->empresa),
        ];
    }
}
