<?php

declare(strict_types=1);

namespace App\Exports\ReportesInternos\Sheets;

use App\Models\Transaccion;
use App\Services\ReportesInternos\ReporteCierreMesService;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HojaDetalladoTransacciones implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
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

    public function view(): View
    {
        $service = app(ReporteCierreMesService::class);
        $cuentas = $service->cuentasFiltradas($this->empresaId, $this->ubicacionIds, $this->anios, $this->meses);

        $filas = [];
        foreach ($cuentas as $cuenta) {
            if (! in_array($cuenta->estado, ['PAGADO', 'PAGADO PARCIALMENTE'], true)) {
                continue;
            }

            [$fechaStr, $anio, $mes] = $service->resolverFechaConfirmacionPago($cuenta);
            if ($fechaStr === '' || ! $service->pasaFiltroTemporal($anio, $mes, $this->anios, $this->meses)) {
                continue;
            }

            /** @var \Illuminate\Support\Collection<int, Transaccion> $transacciones */
            $transacciones = $service->transaccionesParaDetalle($cuenta);
            foreach ($transacciones as $transaccion) {
                $filas[] = [
                    'empresa' => $cuenta->empresa?->nombre ?? '',
                    'cuenta_por_cobrar_id' => $cuenta->id,
                    'estado_cuenta_por_cobrar' => $cuenta->estado,
                    'estado_transaccion' => $transaccion->estado,
                    'tipo_transaccion' => $transaccion->tipo,
                    'fecha_confirmacion_pago' => $fechaStr,
                    'subtotal' => (float) $transaccion->monto + (float) $transaccion->comision,
                    'comision' => (float) $transaccion->comision,
                    'costo_producto' => (float) $transaccion->monto,
                    'centro_costo_dispersion' => $service->centroCostoDispersion($transaccion),
                    'centro_costo_cobro' => $cuenta->centro_costo ?? '',
                ];
            }
        }

        return view('exports.reportes_internos.hoja_detallado_transacciones', [
            'filas' => $filas,
        ]);
    }

    public function title(): string
    {
        return 'Reporte Detallado Por Transacción';
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('3148c8');

        $sheet->getStyle('A1:K1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->getStyle('A1:K1')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
    }
}
