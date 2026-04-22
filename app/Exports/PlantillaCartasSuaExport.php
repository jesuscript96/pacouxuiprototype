<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantillaCartasSuaExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @return array<int, PlantillaCartasSuaSheet|InstruccionesCartasSuaSheet>
     */
    public function sheets(): array
    {
        return [
            new PlantillaCartasSuaSheet,
            new InstruccionesCartasSuaSheet,
        ];
    }
}
