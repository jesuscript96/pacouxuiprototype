<?php

namespace App\Exports\Reclutamiento;

use App\Exports\Reclutamiento\Sheets\CandidatosSheet;
use App\Models\Vacante;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CandidatosVacanteExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private Vacante $vacante,
        private ?string $estatus = null,
    ) {}

    public function sheets(): array
    {
        return [
            new CandidatosSheet($this->vacante, $this->estatus),
        ];
    }
}
