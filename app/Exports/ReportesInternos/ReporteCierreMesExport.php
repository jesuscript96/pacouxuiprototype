<?php

declare(strict_types=1);

namespace App\Exports\ReportesInternos;

use App\Exports\ReportesInternos\Sheets\HojaDetalladoTransacciones;
use App\Exports\ReportesInternos\Sheets\HojaGeneralCierreMes;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReporteCierreMesExport implements WithMultipleSheets
{
    use Exportable;

    /**
     * @param  list<int|string>  $ubicacionIds
     * @param  list<int|string>  $anios
     * @param  list<int|string>  $meses
     */
    public function __construct(
        protected int $empresaId,
        protected array $ubicacionIds,
        protected array $anios,
        protected array $meses,
    ) {}

    public function sheets(): array
    {
        return [
            new HojaGeneralCierreMes($this->empresaId, $this->ubicacionIds, $this->anios, $this->meses),
            new HojaDetalladoTransacciones($this->empresaId, $this->ubicacionIds, $this->anios, $this->meses),
        ];
    }
}
