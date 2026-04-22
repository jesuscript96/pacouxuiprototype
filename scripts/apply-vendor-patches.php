<?php

declare(strict_types=1);

/**
 * Aplica en vendor los mismos cambios que antes vivían en patches/*.patch.
 * Evita depender del binario `patch` (problemático en Windows / PowerShell).
 */
$root = dirname(__DIR__);

$livewireFile = $root.'/vendor/livewire/livewire/src/Features/SupportFileUploads/TemporaryUploadedFile.php';
if (is_file($livewireFile)) {
    $content = file_get_contents($livewireFile);
    if (! str_contains($content, '$tmpFile = \\tmpfile()')) {
        $updated = str_replace('$tmpFile = tmpfile()', '$tmpFile = \\tmpfile()', $content);
        if ($updated !== $content) {
            file_put_contents($livewireFile, $updated);
        }
    }
}

$spreadsheetFile = $root.'/vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Shared/File.php';
if (is_file($spreadsheetFile)) {
    $content = file_get_contents($spreadsheetFile);
    if (str_contains($content, 'Rutas internas de un ZIP')) {
        exit(0);
    }

    $normalized = str_replace(["\r\n", "\r"], "\n", $content);
    $needle = "        \$returnValue = '';\n\n        // Try using realpath()";
    $block = <<<'PHP'
        $returnValue = '';

        // Rutas internas de un ZIP (xlsx/ods): no llamar file_exists
        // (dispara open_basedir en hosting compartido con RunCloud/Docker)
        if (preg_match('#^/?(\[Content_Types\]|_rels/|docProps/|xl/)#', $filename)) {
            $pathArray = array_filter(explode('/', trim($filename, '/')));
            $pathArray = array_values($pathArray);
            return implode('/', $pathArray);
        }

        // Try using realpath()
PHP;
    if (! str_contains($normalized, $needle)) {
        exit(0);
    }

    $updated = str_replace($needle, $block, $normalized);
    $eol = str_contains($content, "\r\n") ? "\r\n" : "\n";
    if ($eol !== "\n") {
        $updated = str_replace("\n", $eol, $updated);
    }
    file_put_contents($spreadsheetFile, $updated);
}
