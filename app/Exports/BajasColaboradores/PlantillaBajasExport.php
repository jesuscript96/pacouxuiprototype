<?php

declare(strict_types=1);

namespace App\Exports\BajasColaboradores;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PlantillaBajasExport implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStyles
{
    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return [];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'email',
            'numero_colaborador',
            'fecha_baja',
            'motivo',
            'comentarios',
        ];
    }

    /**
     * @return array<string, int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 20,
            'C' => 15,
            'D' => 25,
            'E' => 40,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
            2 => [
                'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
            ],
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $sheet->setCellValue('A2', 'colaborador@empresa.com');
                $sheet->setCellValue('B2', 'EMP-001');
                $sheet->setCellValue('C2', now()->format('Y-m-d'));
                $sheet->setCellValue('D2', 'RENUNCIA');
                $sheet->setCellValue('E2', 'Comentario opcional');

                $sheet->getComment('A1')->getText()->createTextRun(
                    "Email del colaborador.\nDebe coincidir exactamente con el registrado en el sistema."
                );
                $sheet->getComment('B1')->getText()->createTextRun(
                    "Número de colaborador.\nSe usa si el email está vacío."
                );
                $sheet->getComment('C1')->getText()->createTextRun(
                    "Formato: YYYY-MM-DD\nEjemplo: 2026-03-25\n\nFechas futuras = baja programada\nFecha hoy/pasada = baja inmediata"
                );
                $sheet->getComment('D1')->getText()->createTextRun(
                    "Motivos válidos:\n- ABANDONO\n- DESPIDO\n- FALLECIMIENTO\n- RENUNCIA\n- TERMINO_CONTRATO"
                );
            },
        ];
    }
}
