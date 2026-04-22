<?php

namespace App\Services\ValidacionDocumental;

/**
 * Servicio para generar y validar CURP.
 *
 * Algoritmo estándar mexicano (RENAPO) — no requiere API externa.
 * Legacy: app/Traits/CurpValidation/CurpValidationTrait.php (usa API en config('services.curp.*'))
 *
 * Para validación oficial contra RENAPO, usar consultarPorApi() cuando se tengan credenciales.
 */
class CurpService
{
    /** @var array<string, string> */
    private const ESTADOS = [
        'AGUASCALIENTES' => 'AS', 'BAJA CALIFORNIA' => 'BC', 'BAJA CALIFORNIA SUR' => 'BS',
        'CAMPECHE' => 'CC', 'COAHUILA' => 'CL', 'COLIMA' => 'CM', 'CHIAPAS' => 'CS',
        'CHIHUAHUA' => 'CH', 'CIUDAD DE MEXICO' => 'DF', 'DISTRITO FEDERAL' => 'DF',
        'DURANGO' => 'DG', 'GUANAJUATO' => 'GT', 'GUERRERO' => 'GR', 'HIDALGO' => 'HG',
        'JALISCO' => 'JC', 'MEXICO' => 'MC', 'ESTADO DE MEXICO' => 'MC', 'MICHOACAN' => 'MN',
        'MORELOS' => 'MS', 'NAYARIT' => 'NT', 'NUEVO LEON' => 'NL', 'OAXACA' => 'OC',
        'PUEBLA' => 'PL', 'QUERETARO' => 'QT', 'QUINTANA ROO' => 'QR', 'SAN LUIS POTOSI' => 'SP',
        'SINALOA' => 'SL', 'SONORA' => 'SR', 'TABASCO' => 'TC', 'TAMAULIPAS' => 'TS',
        'TLAXCALA' => 'TL', 'VERACRUZ' => 'VZ', 'YUCATAN' => 'YN', 'ZACATECAS' => 'ZS',
        'NACIDO EN EL EXTRANJERO' => 'NE',
    ];

    /** @var list<string> */
    private const INCONVENIENTES = [
        'BACA', 'BAKA', 'BUEI', 'BUEY', 'CACA', 'CACO', 'CAGA', 'CAGO', 'CAKA', 'CAKO',
        'COGE', 'COGI', 'COJA', 'COJE', 'COJI', 'COJO', 'COLA', 'CULO', 'FALO', 'FETO',
        'GETA', 'GUEI', 'GUEY', 'JETA', 'JOTO', 'KACA', 'KACO', 'KAGA', 'KAGO', 'KAKA',
        'KAKO', 'KOGE', 'KOGI', 'KOJA', 'KOJE', 'KOJI', 'KOJO', 'KOLA', 'KULO', 'LILO',
        'LOCA', 'LOCO', 'LOKA', 'LOKO', 'MAME', 'MAMO', 'MEAR', 'MEAS', 'MEON', 'MIAR',
        'MION', 'MOCO', 'MOKO', 'MULA', 'MULO', 'NACA', 'NACO', 'PEDA', 'PEDO', 'PENE',
        'PIPI', 'PITO', 'POPO', 'PUTA', 'PUTO', 'QULO', 'RATA', 'ROBA', 'ROBE', 'ROBO',
        'RUIN', 'SENO', 'TETA', 'VACA', 'VAGA', 'VAGO', 'VAKA', 'VUEI', 'VUEY', 'WUEI', 'WUEY',
    ];

    /** @var list<string> */
    private const PALABRAS_COMUNES = ['MARIA', 'MA.', 'MA', 'JOSE', 'J.', 'J'];

    private const DICCIONARIO = '0123456789ABCDEFGHIJKLMNÑOPQRSTUVWXYZ';

    /**
     * Generar CURP a partir de datos personales.
     *
     * @param  string  $sexo  H o M
     * @param  string  $fechaNacimiento  formato YYYY-MM-DD
     */
    public function generar(
        string $nombre,
        string $apellidoPaterno,
        string $apellidoMaterno,
        string $fechaNacimiento,
        string $sexo,
        string $estado,
    ): string {
        $nombre = $this->limpiar($nombre);
        $apellidoPaterno = $this->limpiar($apellidoPaterno);
        $apellidoMaterno = $this->limpiar($apellidoMaterno);
        $sexo = strtoupper(substr($sexo, 0, 1));

        $nombre = $this->eliminarPalabrasComunes($nombre);

        // Posiciones 1-4: iniciales
        $curp = $this->primeraLetra($apellidoPaterno);
        $curp .= $this->primeraVocalInterna($apellidoPaterno);
        $curp .= $this->primeraLetra($apellidoMaterno !== '' ? $apellidoMaterno : 'X');
        $curp .= $this->primeraLetra($nombre);

        if (in_array($curp, self::INCONVENIENTES, true)) {
            $curp[1] = 'X';
        }

        // Posiciones 5-10: fecha AAMMDD
        $fecha = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaNacimiento);
        $curp .= $fecha->format('y');
        $curp .= $fecha->format('m');
        $curp .= $fecha->format('d');

        // Posición 11: sexo
        $curp .= $sexo;

        // Posiciones 12-13: entidad federativa
        $estadoNormalizado = $this->limpiar($estado);
        $curp .= self::ESTADOS[$estadoNormalizado] ?? 'NE';

        // Posiciones 14-16: consonantes internas
        $curp .= $this->primeraConsonanteInterna($apellidoPaterno);
        $curp .= $this->primeraConsonanteInterna($apellidoMaterno !== '' ? $apellidoMaterno : 'X');
        $curp .= $this->primeraConsonanteInterna($nombre);

        // Posición 17: homoclave (simplificado — producción consulta RENAPO)
        $anio = (int) $fecha->format('Y');
        $curp .= $anio < 2000 ? '0' : 'A';

        // Posición 18: dígito verificador
        $curp .= $this->calcularDigitoVerificador($curp);

        return $curp;
    }

    /**
     * Validar formato de CURP (18 caracteres, patrón oficial).
     */
    public function validarFormato(string $curp): bool
    {
        return (bool) preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{5}[A-Z0-9]\d$/', strtoupper($curp));
    }

    /**
     * Obtener código de entidad federativa.
     */
    public function codigoEstado(string $estado): ?string
    {
        return self::ESTADOS[$this->limpiar($estado)] ?? null;
    }

    private function limpiar(string $texto): string
    {
        $texto = strtoupper(trim($texto));
        $texto = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'],
            ['A', 'E', 'I', 'O', 'U', 'X', 'U'],
            $texto,
        );

        return preg_replace('/[^A-Z\s]/', '', $texto) ?? $texto;
    }

    private function eliminarPalabrasComunes(string $nombre): string
    {
        $partes = explode(' ', $nombre);

        if (count($partes) > 1) {
            foreach (self::PALABRAS_COMUNES as $palabra) {
                if ($partes[0] === $palabra) {
                    array_shift($partes);

                    break;
                }
            }
        }

        return implode(' ', $partes);
    }

    private function primeraLetra(string $texto): string
    {
        return $texto !== '' ? $texto[0] : 'X';
    }

    private function primeraVocalInterna(string $texto): string
    {
        $vocales = ['A', 'E', 'I', 'O', 'U'];
        $len = strlen($texto);

        for ($i = 1; $i < $len; $i++) {
            if (in_array($texto[$i], $vocales, true)) {
                return $texto[$i];
            }
        }

        return 'X';
    }

    private function primeraConsonanteInterna(string $texto): string
    {
        $vocales = ['A', 'E', 'I', 'O', 'U'];
        $len = strlen($texto);

        for ($i = 1; $i < $len; $i++) {
            if (! in_array($texto[$i], $vocales, true) && ctype_alpha($texto[$i])) {
                return $texto[$i];
            }
        }

        return 'X';
    }

    private function calcularDigitoVerificador(string $curp17): string
    {
        $suma = 0;

        for ($i = 0; $i < 17; $i++) {
            $pos = mb_strpos(self::DICCIONARIO, $curp17[$i]);
            $suma += ($pos !== false ? $pos : 0) * (18 - $i);
        }

        $digito = (10 - ($suma % 10)) % 10;

        return (string) $digito;
    }
}
