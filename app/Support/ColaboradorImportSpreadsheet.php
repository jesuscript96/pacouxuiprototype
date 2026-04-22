<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class ColaboradorImportSpreadsheet
{
    /**
     * Hoja de datos: primero por título oficial; si no, primera hoja cuyo A1 sea encabezado conocido
     * (plantillas guardadas como "Sheet1" o Excel dejó activa la hoja "Instrucciones").
     */
    public static function resolveDataSheet(Spreadsheet $spreadsheet): Worksheet
    {
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            if ($sheet->getTitle() === 'Colaboradores') {
                return $sheet;
            }
        }

        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $a1 = strtolower(trim((string) $sheet->getCell('A1')->getValue()));
            if (in_array($a1, ['user_id', 'name', 'nombre'], true)) {
                return $sheet;
            }
        }

        return $spreadsheet->getActiveSheet();
    }
}
