<?php

declare(strict_types=1);

namespace App\Imports\BajasColaboradores;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Services\ColaboradorBajaService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class BajasMasivasImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    protected int $empresaId;

    protected ColaboradorBajaService $service;

    /** @var list<array{fila: int, status: string, mensaje: string}> */
    protected array $resultados = [];

    protected int $procesadas = 0;

    protected int $errores = 0;

    public function __construct(int $empresaId)
    {
        $this->empresaId = $empresaId;
        $this->service = app(ColaboradorBajaService::class);
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $fila = $index + 2;

            if ($this->esFilaEjemplo($row)) {
                continue;
            }

            try {
                $this->procesarFila($row->toArray(), $fila);
                $this->procesadas++;
                $this->resultados[] = [
                    'fila' => $fila,
                    'status' => 'success',
                    'mensaje' => 'Baja registrada correctamente',
                ];
            } catch (Throwable $e) {
                $this->errores++;
                $this->resultados[] = [
                    'fila' => $fila,
                    'status' => 'error',
                    'mensaje' => $e->getMessage(),
                ];
                Log::warning("Baja masiva - Error fila {$fila}: {$e->getMessage()}");
            }
        }
    }

    protected function esFilaEjemplo(Collection $row): bool
    {
        $email = trim((string) ($row['email'] ?? ''));
        $numero = trim((string) ($row['numero_colaborador'] ?? ''));

        return $email === 'colaborador@empresa.com'
            || ($email === '' && $numero === 'EMP-001');
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function procesarFila(array $row, int $fila): void
    {
        $this->validarFila($row, $fila);
        $colaborador = $this->buscarColaborador($row, $fila);

        $data = [
            'fecha_baja' => $this->parsearFecha($row['fecha_baja']),
            'motivo' => strtoupper(trim((string) $row['motivo'])),
            'comentarios' => isset($row['comentarios']) && $row['comentarios'] !== null && $row['comentarios'] !== ''
                ? trim((string) $row['comentarios'])
                : null,
        ];

        try {
            $this->service->registrarBaja($colaborador, $data);
        } catch (ValidationException $e) {
            throw new \RuntimeException((string) collect($e->errors())->flatten()->first());
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function validarFila(array $row, int $fila): void
    {
        $validator = Validator::make($row, [
            'email' => 'nullable|email',
            'numero_colaborador' => 'nullable|string',
            'fecha_baja' => 'required',
            'motivo' => 'required|string',
        ], [
            'fecha_baja.required' => "Fila {$fila}: La fecha de baja es obligatoria",
            'motivo.required' => "Fila {$fila}: El motivo es obligatorio",
        ]);

        if ($validator->fails()) {
            throw new \RuntimeException((string) $validator->errors()->first());
        }

        $email = isset($row['email']) ? trim((string) $row['email']) : '';
        $numero = isset($row['numero_colaborador']) ? trim((string) $row['numero_colaborador']) : '';

        if ($email === '' && $numero === '') {
            throw new \RuntimeException("Fila {$fila}: Debe proporcionar email o número de colaborador");
        }

        $motivosValidos = array_keys(BajaColaborador::motivosDisponibles());
        $motivo = strtoupper(trim((string) $row['motivo']));
        if (! in_array($motivo, $motivosValidos, true)) {
            throw new \RuntimeException("Fila {$fila}: Motivo inválido '{$row['motivo']}'. Válidos: ".implode(', ', $motivosValidos));
        }

        try {
            $this->parsearFecha($row['fecha_baja']);
        } catch (Throwable) {
            throw new \RuntimeException("Fila {$fila}: Formato de fecha inválido. Use YYYY-MM-DD");
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function buscarColaborador(array $row, int $fila): Colaborador
    {
        $query = Colaborador::query()
            ->where('empresa_id', $this->empresaId)
            ->whereNull('deleted_at');

        $email = isset($row['email']) ? trim((string) $row['email']) : '';
        if ($email !== '') {
            $colaborador = (clone $query)->where('email', $email)->first();
            if ($colaborador !== null) {
                return $colaborador;
            }
        }

        $numero = isset($row['numero_colaborador']) ? trim((string) $row['numero_colaborador']) : '';
        if ($numero !== '') {
            $colaborador = (clone $query)->where('numero_colaborador', $numero)->first();
            if ($colaborador !== null) {
                return $colaborador;
            }
        }

        $identificador = $email !== '' ? $email : ($numero !== '' ? $numero : 'desconocido');
        throw new \RuntimeException("Fila {$fila}: Colaborador no encontrado ({$identificador})");
    }

    protected function parsearFecha(mixed $fecha): string
    {
        if ($fecha === null || $fecha === '') {
            throw new \InvalidArgumentException('Fecha vacía');
        }

        if (is_numeric($fecha)) {
            return Carbon::createFromTimestamp(ExcelDate::excelToTimestamp((float) $fecha))->format('Y-m-d');
        }

        $fecha = trim((string) $fecha);
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $formato) {
            try {
                return Carbon::createFromFormat($formato, $fecha)->format('Y-m-d');
            } catch (Throwable) {
                continue;
            }
        }

        throw new \InvalidArgumentException("Formato de fecha no reconocido: {$fecha}");
    }

    /**
     * @return list<array{fila: int, status: string, mensaje: string}>
     */
    public function getResultados(): array
    {
        return $this->resultados;
    }

    public function getProcesadas(): int
    {
        return $this->procesadas;
    }

    public function getErrores(): int
    {
        return $this->errores;
    }
}
