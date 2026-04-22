<?php

declare(strict_types=1);

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CatalogoColaboradorFilasExport implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
{
    /** Color primario panel Cliente (alineado con otros exports) */
    private const COLOR_ENCABEZADO_FONDO = '3148c8';

    /**
     * @param  array<int, string>  $encabezados
     * @param  array<int, array<int, mixed>>  $filas
     */
    public function __construct(
        private readonly string $tituloHoja,
        private readonly array $encabezados,
        private readonly array $filas,
    ) {}

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return $this->filas;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return $this->encabezados;
    }

    public function title(): string
    {
        return $this->tituloHoja;
    }

    /**
     * @return array<class-string, callable(AfterSheet): void>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $evento): void {
                $hoja = $evento->sheet->getDelegate();
                $ultimaFila = $hoja->getHighestRow();
                $ultimaColumna = $hoja->getHighestColumn();

                $estiloEncabezado = [
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => self::COLOR_ENCABEZADO_FONDO],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1F2937'],
                        ],
                    ],
                ];

                $hoja->getStyle('A1:'.$ultimaColumna.'1')->applyFromArray($estiloEncabezado);
                $hoja->getRowDimension(1)->setRowHeight(26);

                if ($ultimaFila > 1) {
                    $hoja->getStyle('A2:'.$ultimaColumna.$ultimaFila)->applyFromArray([
                        'font' => [
                            'size' => 11,
                            'name' => 'Calibri',
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D1D5DB'],
                            ],
                        ],
                    ]);

                    for ($fila = 2; $fila <= $ultimaFila; $fila++) {
                        if ($fila % 2 === 0) {
                            $hoja->getStyle('A'.$fila.':'.$ultimaColumna.$fila)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'F3F4F6'],
                                ],
                            ]);
                        }
                    }
                }

                $hoja->freezePane('A2');
                $hoja->setAutoFilter('A1:'.$ultimaColumna.'1');
            },
        ];
    }
}
