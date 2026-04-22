<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\DocumentoCorporativo;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class DocumentosCorporativosDestinatariosExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithTitle
{
    /** Color primario panel Cliente (Paco) */
    private const COLOR_ENCABEZADO_FONDO = '3148c8';

    public function __construct(
        protected Builder $consulta,
    ) {
        $this->consulta = clone $consulta;
    }

    public function query(): Builder
    {
        return $this->consulta
            ->with([
                'colaborador.empresa',
                'carpeta',
            ])
            ->orderByDesc('documentos_corporativos.fecha_carga');
    }

    public function title(): string
    {
        return 'Destinatarios documentos';
    }

    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 34,
            'B' => 28,
            'C' => 26,
            'D' => 20,
            'E' => 32,
            'F' => 20,
            'G' => 22,
            'H' => 22,
            'I' => 18,
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Colaborador',
            'Empresa',
            'Carpeta',
            'Subcarpeta',
            'Documento',
            'Fecha de carga',
            'Primera visualización',
            'Última visualización',
            'Estado de lectura',
        ];
    }

    /**
     * @param  DocumentoCorporativo  $fila
     * @return array<int, string|null>
     */
    public function map($fila): array
    {
        $colaborador = $fila->colaborador;
        $sinLectura = $fila->primera_visualizacion === null && $fila->ultima_visualizacion === null;
        $formatoFecha = 'd/m/Y H:i';

        return [
            $colaborador?->nombre_completo,
            $colaborador?->empresa?->nombre,
            $fila->carpeta?->nombre,
            $fila->subcarpeta,
            $fila->nombre_documento,
            $fila->fecha_carga?->format($formatoFecha),
            $fila->primera_visualizacion?->format($formatoFecha),
            $fila->ultima_visualizacion?->format($formatoFecha),
            $sinLectura ? 'No visualizado' : 'Visualizado',
        ];
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
                $hoja->getRowDimension(1)->setRowHeight(24);

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

                    // Celdas alternas (cebra suave)
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

                    // Columna estado (I): centrado
                    $hoja->getStyle('I2:I'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Fechas (F, G, H): alineación centrada
                    $hoja->getStyle('F2:H'.$ultimaFila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $hoja->freezePane('A2');
                $hoja->setAutoFilter('A1:'.$ultimaColumna.'1');

                // Vista al imprimir / PDF
                $hoja->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                $hoja->getPageSetup()->setFitToWidth(1);
                $hoja->getPageSetup()->setFitToHeight(0);
                $hoja->getPageMargins()->setTop(0.75)->setRight(0.5)->setLeft(0.5)->setBottom(0.75);
            },
        ];
    }
}
