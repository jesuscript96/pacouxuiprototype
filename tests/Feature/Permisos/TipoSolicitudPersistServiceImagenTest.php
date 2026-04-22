<?php

declare(strict_types=1);

use App\Services\TipoSolicitudPersistService;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('uploads');
    config(['filesystems.archivos_disk' => 'uploads']);
});

it('mueve imagen de tmp a carpeta del tipo', function (): void {
    Storage::disk('uploads')->put('companies/7/tipos-solicitud/tmp/imagen_2_999.jpg', 'x');

    $service = app(TipoSolicitudPersistService::class);
    $m = new \ReflectionMethod(TipoSolicitudPersistService::class, 'moverImagenDesdeTmpSiAplica');
    $m->setAccessible(true);
    $out = $m->invoke($service, 'companies/7/tipos-solicitud/tmp/imagen_2_999.jpg', 42, 7);

    expect($out)->toBe('companies/7/tipos-solicitud/42/imagen_2_999.jpg');
    expect(Storage::disk('uploads')->exists($out))->toBeTrue();
    expect(Storage::disk('uploads')->exists('companies/7/tipos-solicitud/tmp/imagen_2_999.jpg'))->toBeFalse();
});

it('no altera rutas que ya están fuera de tmp', function (): void {
    $ruta = 'companies/7/tipos-solicitud/42/y.jpg';
    Storage::disk('uploads')->put($ruta, 'z');

    $service = app(TipoSolicitudPersistService::class);
    $m = new \ReflectionMethod(TipoSolicitudPersistService::class, 'moverImagenDesdeTmpSiAplica');
    $m->setAccessible(true);
    $out = $m->invoke($service, $ruta, 42, 7);

    expect($out)->toBe($ruta);
});

it('normaliza URL y mueve desde tmp', function (): void {
    Storage::disk('uploads')->put('companies/3/tipos-solicitud/tmp/imagen_1_111.jpg', 'b');

    $service = app(TipoSolicitudPersistService::class);
    $m = new \ReflectionMethod(TipoSolicitudPersistService::class, 'moverImagenDesdeTmpSiAplica');
    $m->setAccessible(true);
    $out = $m->invoke($service, 'https://bucket.s3.amazonaws.com/companies/3/tipos-solicitud/tmp/imagen_1_111.jpg', 10, 3);

    expect($out)->toBe('companies/3/tipos-solicitud/10/imagen_1_111.jpg');
    expect(Storage::disk('uploads')->exists('companies/3/tipos-solicitud/10/imagen_1_111.jpg'))->toBeTrue();
});

it('no restaura imagen_actual cuando el usuario vació el campo imagen', function (): void {
    $service = app(TipoSolicitudPersistService::class);
    $m = new \ReflectionMethod(TipoSolicitudPersistService::class, 'resolverImagen');
    $m->setAccessible(true);
    $out = $m->invoke($service, [
        'imagen' => null,
        'imagen_actual' => 'companies/1/tipos-solicitud/1/old.jpg',
    ], 1, 1, 1);

    expect($out)->toBe('');
});

it('elimina del disco las imágenes que ya no están en las preguntas guardadas', function (): void {
    Storage::disk('uploads')->put('companies/1/tipos-solicitud/1/a.jpg', 'a');
    Storage::disk('uploads')->put('companies/1/tipos-solicitud/1/b.jpg', 'b');

    $service = app(TipoSolicitudPersistService::class);
    $m = new \ReflectionMethod(TipoSolicitudPersistService::class, 'eliminarImagenesPreguntaHuérfanas');
    $m->setAccessible(true);
    $m->invoke($service, [
        'companies/1/tipos-solicitud/1/a.jpg',
        'companies/1/tipos-solicitud/1/b.jpg',
    ], [
        'companies/1/tipos-solicitud/1/a.jpg',
    ]);

    expect(Storage::disk('uploads')->exists('companies/1/tipos-solicitud/1/a.jpg'))->toBeTrue();
    expect(Storage::disk('uploads')->exists('companies/1/tipos-solicitud/1/b.jpg'))->toBeFalse();
});
