<?php

declare(strict_types=1);

namespace App\Exports\ReportesInternos\Sheets;

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

class HojaGeneralCierreMes implements FromView, ShouldAutoSize, WithStyles, WithTitle
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

            $estadoCuenta = $cuenta->estadoCuenta;
            if ($estadoCuenta === null) {
                continue;
            }

            $txColl = $estadoCuenta->transacciones
                ->where('estado', ReporteCierreMesService::ESTADO_TRANSACCION_EXITOSA)
                ->where('tipo_pago', ReporteCierreMesService::TIPO_PAGO_SALDO_SISTEMA);

            $penalizaciones = (float) $txColl->where('tipo', ReporteCierreMesService::TIPO_PENALIZACION)->sum('monto');
            $costoProducto = (float) $txColl->where('tipo', '!=', ReporteCierreMesService::TIPO_PENALIZACION)->sum('monto');
            $comisiones = (float) $txColl->where('tipo', '!=', ReporteCierreMesService::TIPO_PENALIZACION)->sum('comision');
            $subtotal = $costoProducto + $comisiones;

            $filas[] = [
                'cuenta_por_cobrar_id' => $cuenta->id,
                'user_id' => $cuenta->user_id,
                'empresa' => $cuenta->empresa?->nombre ?? '',
                'ubicacion' => $cuenta->ubicacion?->nombre ?? '',
                'fecha_confirmacion_pago' => $fechaStr,
                'tipo_confirmacion' => $service->etiquetaTipoConfirmacion($cuenta->tipo_confirmacion_pago),
                'penalizaciones' => $penalizaciones,
                'subtotal' => $subtotal,
                'comisiones' => $comisiones,
                'costo_producto' => $costoProducto,
                'total_cuenta' => (float) $cuenta->debe,
                'comisiones_bancarias' => $cuenta->comisiones_bancarias !== null ? (float) $cuenta->comisiones_bancarias : '',
            ];
        }

        return view('exports.reportes_internos.hoja_general_cierre_mes', [
            'filas' => $filas,
        ]);
    }

    public function title(): string
    {
        return 'Reporte General De Cierre De Mes';
    }

    public function styles(Worksheet $sheet): void
    {
        $sheet->getStyle('A1:L1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('3148c8');

        $sheet->getStyle('A1:L1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getRowDimension(1)->setRowHeight(25);

        $sheet->getStyle('A1:L1')->getFont()->getColor()->setARGB(Color::COLOR_WHITE);
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
    }
}
