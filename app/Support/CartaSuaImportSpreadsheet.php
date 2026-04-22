<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class CartaSuaImportSpreadsheet
{
    /**
     * BL: Mapeo de encabezados legibles (legacy) a nombres internos.
     * Soporta tanto la plantilla legacy ("Número de empleado") como snake_case ("numero_empleado").
     *
     * @var array<string, string>
     */
    private const HEADER_MAP = [
        'número de empleado' => 'numero_empleado',
        'numero de empleado' => 'numero_empleado',
        'numero_empleado' => 'numero_empleado',
        'numero_de_empleado' => 'numero_empleado',
        'razón social' => 'razon_social',
        'razon social' => 'razon_social',
        'razon_social' => 'razon_social',
        'rfc' => 'rfc',
        'curp' => 'curp',
        'nombre' => 'nombre_completo',
        'nombre_completo' => 'nombre_completo',
        'nombre completo' => 'nombre_completo',
        'retiro' => 'retiro',
        'c.v.' => 'cv',
        'cv' => 'cv',
        'cesantia_vejez' => 'cv',
        'infonavit' => 'infonavit',
        'tot rcv_inf' => 'total',
        'total rcv_inf' => 'total',
        'total_rcvinf' => 'total',
        'total' => 'total',
        'bimestre' => 'bimestre',
    ];

    /**
     * Hoja de datos: busca por títulos conocidos; si no, primera hoja cuyo A1 sea encabezado reconocido.
     */
    public static function resolveDataSheet(Spreadsheet $spreadsheet): Worksheet
    {
        $titulosConocidos = ['Cartas SUA', 'Carga de registros'];

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if (in_array($sheet->getTitle(), $titulosConocidos, true)) {
                return $sheet;
            }
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $a1 = strtolower(trim((string) $sheet->getCell('A1')->getValue()));
            if (isset(self::HEADER_MAP[$a1])) {
                return $sheet;
            }
        }

        return $spreadsheet->getActiveSheet();
    }

    /**
     * Normaliza los encabezados del Excel a nombres internos.
     *
     * @param  list<string>  $headers  Encabezados crudos del Excel
     * @return list<string> Encabezados normalizados
     */
    public static function normalizeHeaders(array $headers): array
    {
        return array_map(function (string $header): string {
            $normalized = strtolower(trim($header));

            return self::HEADER_MAP[$normalized] ?? $normalized;
        }, $headers);
    }

    /**
     * Normaliza una fila de datos crudos del Excel.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeRow(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if (is_string($key) && $value !== null && $value !== '') {
                $out[$key] = trim((string) $value);
            }
        }

        $numericFields = ['retiro', 'cv', 'infonavit', 'total'];
        foreach ($numericFields as $field) {
            if (isset($out[$field])) {
                $limpio = preg_replace('/[^0-9.\-]/', '', $out[$field]);
                $out[$field] = round((float) $limpio, 2);
            }
        }

        return $out;
    }

    /**
     * Valida una fila normalizada. Retorna array de mensajes de error (vacío = válida).
     *
     * @param  array<string, mixed>  $data
     * @return list<string>
     */
    public static function validateRow(array $data): array
    {
        $errores = [];

        if (empty($data['numero_empleado'] ?? '')) {
            $errores[] = 'Número de empleado vacío';
        }

        if (empty($data['razon_social'] ?? '')) {
            $errores[] = 'Razón social vacía';
        }

        $rfc = $data['rfc'] ?? '';
        if (strlen($rfc) < 10 || strlen($rfc) > 13) {
            $errores[] = 'RFC inválido (debe tener 10-13 caracteres)';
        }

        $curp = $data['curp'] ?? '';
        if (strlen($curp) < 18) {
            $errores[] = 'CURP inválido (debe tener 18 caracteres)';
        }

        if (empty($data['nombre_completo'] ?? '')) {
            $errores[] = 'Nombre completo vacío';
        }

        if (empty($data['bimestre'] ?? '')) {
            $errores[] = 'Bimestre vacío';
        }

        $total = (float) ($data['total'] ?? 0);
        if ($total <= 0) {
            $errores[] = 'Total debe ser mayor a 0';
        }

        return $errores;
    }
}
