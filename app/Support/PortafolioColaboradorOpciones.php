<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Empresa;
use App\Models\OpcionesPortafolio;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo fijo de campos del expediente del colaborador (legacy OptionsPortfolioController).
 * `nombre` = identificador (slug); `opcion` = etiqueta visible en UI / app.
 */
final class PortafolioColaboradorOpciones
{
    /**
     * @return list<array{nombre: string, opcion: string}>
     */
    public static function definiciones(): array
    {
        return [
            ['nombre' => 'full_name', 'opcion' => 'Nombre Completo'],
            ['nombre' => 'email', 'opcion' => 'Correo Electrónico'],
            ['nombre' => 'mobile', 'opcion' => 'Celular'],
            ['nombre' => 'gender', 'opcion' => 'Género'],
            ['nombre' => 'birthdate', 'opcion' => 'Fecha de Nacimiento'],
            ['nombre' => 'employee_number', 'opcion' => 'Número de Colaborador'],
            ['nombre' => 'rfc', 'opcion' => 'RFC'],
            ['nombre' => 'curp', 'opcion' => 'CURP'],
            ['nombre' => 'social_security_number', 'opcion' => 'Número de Seguridad Social'],
            ['nombre' => 'admission_date', 'opcion' => 'Fecha de Contratación'],
            ['nombre' => 'antiquity', 'opcion' => 'Antigüedad'],
            ['nombre' => 'employer_business_name', 'opcion' => 'Empleador (Razón social)'],
            ['nombre' => 'payment_frequency', 'opcion' => 'Periodicidad de Pago'],
            ['nombre' => 'department', 'opcion' => 'Departamento'],
            ['nombre' => 'area', 'opcion' => 'Área'],
            ['nombre' => 'position', 'opcion' => 'Puesto'],
            ['nombre' => 'location', 'opcion' => 'Ubicación (Centro de Trabajo)'],
        ];
    }

    /**
     * Estado inicial para formulario (toggles por `nombre`).
     *
     * @return array<string, bool>
     */
    public static function defaultsParaEmpresa(Empresa $empresa): array
    {
        $activos = $empresa->opcionesPortafolio()
            ->pluck('nombre')
            ->all();

        $data = [];
        foreach (self::definiciones() as $def) {
            $data[$def['nombre']] = in_array($def['nombre'], $activos, true);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data  claves = `nombre` de cada definición, valores = bool
     */
    public static function sincronizar(Empresa $empresa, array $data): void
    {
        DB::transaction(function () use ($empresa, $data): void {
            foreach (self::definiciones() as $def) {
                $nombre = $def['nombre'];
                $enabled = (bool) ($data[$nombre] ?? false);
                $query = $empresa->opcionesPortafolio()->where('nombre', $nombre);

                if ($enabled) {
                    if (! $query->exists()) {
                        OpcionesPortafolio::query()->create([
                            'empresa_id' => $empresa->id,
                            'nombre' => $nombre,
                            'opcion' => $def['opcion'],
                        ]);
                    }
                } elseif ($query->exists()) {
                    $query->delete();
                }
            }
        });
    }

    /**
     * @return list<Toggle>
     */
    public static function togglesFormulario(): array
    {
        $toggles = [];
        foreach (self::definiciones() as $def) {
            $toggles[] = Toggle::make($def['nombre'])
                ->label($def['opcion'])
                ->default(false);
        }

        return $toggles;
    }
}
