<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Str;

/**
 * BL: Nombres de archivo en documentos corporativos (disco público uploads): sin acentos, ñ→n, espacios→_.
 */
final class NombreArchivoDocumentosCorporativos
{
    /**
     * Normaliza el nombre de archivo conservando la extensión (en minúsculas).
     */
    public static function normalizarParaAlmacenamiento(string $nombreArchivo): string
    {
        $nombreArchivo = basename($nombreArchivo);
        $info = pathinfo($nombreArchivo);
        $base = (string) ($info['filename'] ?? $nombreArchivo);
        $ext = isset($info['extension']) && $info['extension'] !== ''
            ? '.'.strtolower($info['extension'])
            : '';

        $base = str_replace(['ñ', 'Ñ'], ['n', 'N'], $base);
        $base = Str::ascii($base);
        $base = str_replace(' ', '_', $base);
        $base = (string) preg_replace('/_+/', '_', $base);
        $base = trim($base, '_');

        if ($base === '') {
            $base = 'archivo';
        }

        return $base.$ext;
    }

    /**
     * Normaliza y, si ya existe en el directorio, añade _1, _2, … antes de la extensión.
     */
    public static function normalizarYAsegurarUnicoEnDirectorio(
        Filesystem $disk,
        string $directorioRelativo,
        string $nombreArchivoOriginal,
    ): string {
        $norm = self::normalizarParaAlmacenamiento($nombreArchivoOriginal);
        $directorioRelativo = trim($directorioRelativo, '/');
        $rutaCompleta = $directorioRelativo === '' ? $norm : $directorioRelativo.'/'.$norm;
        if (! $disk->exists($rutaCompleta)) {
            return $norm;
        }

        $info = pathinfo($norm);
        $base = (string) ($info['filename'] ?? 'archivo');
        $ext = isset($info['extension']) && $info['extension'] !== ''
            ? '.'.$info['extension']
            : '';

        $i = 1;
        do {
            $candidate = $base.'_'.$i.$ext;
            $rutaCompleta = $directorioRelativo === '' ? $candidate : $directorioRelativo.'/'.$candidate;
            $i++;
        } while ($disk->exists($rutaCompleta));

        return $candidate;
    }
}
