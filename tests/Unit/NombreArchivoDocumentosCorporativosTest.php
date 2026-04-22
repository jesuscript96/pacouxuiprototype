<?php

declare(strict_types=1);

use App\Support\NombreArchivoDocumentosCorporativos;
use Illuminate\Support\Facades\Storage;

uses(Tests\TestCase::class);

it('normaliza acentos espacios y extension', function (): void {
    expect(NombreArchivoDocumentosCorporativos::normalizarParaAlmacenamiento('foto 1 .jpg'))
        ->toBe('foto_1.jpg')
        ->and(NombreArchivoDocumentosCorporativos::normalizarParaAlmacenamiento('niño niña.JPEG'))
        ->toBe('nino_nina.jpeg')
        ->and(NombreArchivoDocumentosCorporativos::normalizarParaAlmacenamiento('café résumé.pdf'))
        ->toBe('cafe_resume.pdf');
});

it('asegura nombre unico en directorio', function (): void {
    Storage::fake('local');
    $disk = Storage::disk('local');
    $disk->put('docs/a.jpg', '1');

    expect(NombreArchivoDocumentosCorporativos::normalizarYAsegurarUnicoEnDirectorio($disk, 'docs', 'a.jpg'))
        ->toBe('a_1.jpg');
});
