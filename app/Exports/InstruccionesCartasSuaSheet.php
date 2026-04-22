<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class InstruccionesCartasSuaSheet implements FromCollection, WithTitle
{
    public function collection(): Collection
    {
        return collect([
            ['Instrucciones para carga masiva de Cartas SUA'],
            [],
            ['Todas las columnas son obligatorias.'],
            [],
            ['Columnas:'],
            ['Número de empleado', 'Número de empleado en el sistema (debe existir en la empresa)'],
            ['Razón social', 'Razón social del patrón registrado en el SUA'],
            ['RFC', 'RFC del trabajador (10 a 13 caracteres)'],
            ['CURP', 'CURP del trabajador (18 caracteres)'],
            ['Nombre', 'Nombre completo del trabajador'],
            ['Retiro', 'Aportación de retiro (numérico, ej: 1500.00)'],
            ['C.V.', 'Cesantía en edad avanzada y vejez (numérico)'],
            ['Infonavit', 'Aportación Infonavit (numérico)'],
            ['Tot RCV_INF', 'Total RCV + Infonavit (numérico, mayor a 0)'],
            ['Bimestre', 'Periodo bimestral (ej: Enero-Febrero 2024)'],
            [],
            ['Notas:'],
            ['- No se permiten duplicados: misma combinación de numero_empleado + bimestre + razon_social.'],
            ['- Los valores monetarios usan punto decimal (ej: 1500.50).'],
            ['- La fila de ejemplo en la hoja "Cartas SUA" debe eliminarse antes de cargar.'],
        ]);
    }

    public function title(): string
    {
        return 'Instrucciones';
    }
}
