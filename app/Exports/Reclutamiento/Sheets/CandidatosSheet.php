<?php

declare(strict_types=1);

namespace App\Exports\Reclutamiento\Sheets;

use App\Models\Vacante;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CandidatosSheet implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    /** @var list<array{etiqueta: string, nombre: string}> */
    private array $camposFormulario;

    public function __construct(
        private Vacante $vacante,
        private ?string $estatus = null,
    ) {
        $this->camposFormulario = $this->vacante
            ->camposFormulario()
            ->where('tipo', '!=', 'file')
            ->orderBy('orden')
            ->get(['etiqueta', 'nombre'])
            ->toArray();
    }

    public function collection(): Collection
    {
        $query = $this->vacante->candidatos();

        if ($this->estatus !== null) {
            $query->where('estatus', $this->estatus);
        }

        return $query
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        $headings = ['#', 'Fecha de postulación', 'Estatus'];

        foreach ($this->camposFormulario as $campo) {
            $headings[] = $campo['etiqueta'];
        }

        $headings[] = 'CURP';
        $headings[] = 'Evaluación CV';

        return $headings;
    }

    /**
     * @param  \App\Models\CandidatoReclutamiento  $candidato
     */
    public function map($candidato): array
    {
        $row = [
            $candidato->id,
            $candidato->created_at->format('d/m/Y H:i'),
            $candidato->estatus,
        ];

        $valores = $candidato->valores_formulario ?? [];
        foreach ($this->camposFormulario as $campo) {
            $valor = $valores[$campo['nombre']] ?? '';

            if (is_array($valor)) {
                $valor = implode(', ', $valor);
            }

            $row[] = $valor;
        }

        $row[] = $candidato->curp ?? '';
        $row[] = $candidato->evaluacion_cv !== null ? "{$candidato->evaluacion_cv}/10" : '';

        return $row;
    }

    public function title(): string
    {
        return 'Candidatos';
    }

    public function styles(Worksheet $sheet): void
    {
        $totalCols = count($this->headings());
        $lastCol = chr(64 + $totalCols);
        $range = "A1:{$lastCol}1";

        $sheet->getStyle($range)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('3148c8');

        $sheet->getStyle($range)->getFont()
            ->getColor()->setARGB(Color::COLOR_WHITE);

        $sheet->getStyle($range)->getFont()->setBold(true);

        $sheet->getStyle($range)->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(25);
    }
}
