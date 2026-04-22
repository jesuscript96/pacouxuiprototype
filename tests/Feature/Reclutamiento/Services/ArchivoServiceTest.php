<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Services\ArchivoService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    Storage::fake('s3');
    config(['filesystems.archivos_disk' => 's3']);
});

describe('ArchivoService', function () {

    describe('guardar', function () {
        it('guarda archivo con estructura correcta de carpetas', function () {
            $empresa = Empresa::factory()->create(['nombre' => 'Paco App']);
            $archivo = UploadedFile::fake()->create('curriculum.pdf', 100);
            $service = app(ArchivoService::class);

            $ruta = $service->guardar(
                archivo: $archivo,
                empresa: $empresa,
                modulo: 'candidatos',
                registroId: 123,
                nombre: 'cv',
            );

            expect($ruta)->toStartWith('companies/')
                ->and($ruta)->toContain('candidatos/123/cv.pdf');
            Storage::disk('s3')->assertExists($ruta);
        });

        it('usa ID de empresa en la ruta', function () {
            $empresa = Empresa::factory()->create(['nombre' => 'Casa de Toño']);
            $archivo = UploadedFile::fake()->image('foto.jpg');
            $service = app(ArchivoService::class);

            $ruta = $service->guardar(
                archivo: $archivo,
                empresa: $empresa,
                modulo: 'candidatos',
                registroId: 1,
            );

            expect($ruta)->toStartWith("companies/{$empresa->id}/");
        });

        it('slugifica el nombre del archivo', function () {
            $empresa = Empresa::factory()->create(['nombre' => 'Test']);
            $archivo = UploadedFile::fake()->create('Mi Documento (1).pdf', 50);
            $service = app(ArchivoService::class);

            $ruta = $service->guardar(
                archivo: $archivo,
                empresa: $empresa,
                modulo: 'docs',
                registroId: 1,
            );

            expect($ruta)->toContain('mi-documento-1.pdf');
        });

        it('permite forzar nombre y extensión', function () {
            $empresa = Empresa::factory()->create(['nombre' => 'Test']);
            $archivo = UploadedFile::fake()->image('foto-original.png');
            $service = app(ArchivoService::class);

            $ruta = $service->guardar(
                archivo: $archivo,
                empresa: $empresa,
                modulo: 'candidatos',
                registroId: 5,
                nombre: 'ine-frente',
                extension: 'jpg',
            );

            expect($ruta)->toEndWith('ine-frente.jpg');
            Storage::disk('s3')->assertExists($ruta);
        });

        it('usa ID incluso si empresa no tiene nombre', function () {
            $empresa = Empresa::factory()->create(['nombre' => '']);
            $archivo = UploadedFile::fake()->create('doc.pdf', 10);
            $service = app(ArchivoService::class);

            $ruta = $service->guardar(
                archivo: $archivo,
                empresa: $empresa,
                modulo: 'test',
                registroId: 1,
            );

            expect($ruta)->toStartWith("companies/{$empresa->id}/");
        });
    });

    describe('guardarContenido', function () {
        it('guarda contenido raw en la ruta correcta', function () {
            $empresa = Empresa::factory()->create();
            $service = app(ArchivoService::class);

            $ruta = $service->guardarContenido(
                contenido: 'contenido del reporte',
                empresa: $empresa,
                modulo: 'reportes',
                registroId: 42,
                nombreConExtension: 'reporte.pdf',
            );

            expect($ruta)->toBe("companies/{$empresa->id}/reportes/42/reporte.pdf");
            Storage::disk('s3')->assertExists($ruta);
            expect(Storage::disk('s3')->get($ruta))->toBe('contenido del reporte');
        });
    });

    describe('eliminar', function () {
        it('elimina archivo existente', function () {
            Storage::disk('s3')->put('test/archivo.txt', 'contenido');
            $service = app(ArchivoService::class);

            $resultado = $service->eliminar('test/archivo.txt');

            expect($resultado)->toBeTrue();
            Storage::disk('s3')->assertMissing('test/archivo.txt');
        });

        it('retorna false si archivo no existe', function () {
            $service = app(ArchivoService::class);

            expect($service->eliminar('no-existe.txt'))->toBeFalse();
        });
    });

    describe('existe', function () {
        it('retorna true si archivo existe', function () {
            Storage::disk('s3')->put('test/archivo.txt', 'contenido');
            $service = app(ArchivoService::class);

            expect($service->existe('test/archivo.txt'))->toBeTrue();
        });

        it('retorna false si archivo no existe', function () {
            $service = app(ArchivoService::class);

            expect($service->existe('no-existe.txt'))->toBeFalse();
        });
    });

    describe('obtener', function () {
        it('retorna contenido del archivo', function () {
            Storage::disk('s3')->put('test/data.txt', 'hola mundo');
            $service = app(ArchivoService::class);

            expect($service->obtener('test/data.txt'))->toBe('hola mundo');
        });

        it('retorna null si archivo no existe', function () {
            $service = app(ArchivoService::class);

            expect($service->obtener('no-existe.txt'))->toBeNull();
        });
    });

    describe('mover', function () {
        it('mueve archivo a nueva ubicación', function () {
            Storage::disk('s3')->put('origen/archivo.txt', 'contenido');
            $service = app(ArchivoService::class);

            $resultado = $service->mover('origen/archivo.txt', 'destino/archivo.txt');

            expect($resultado)->toBeTrue();
            Storage::disk('s3')->assertMissing('origen/archivo.txt');
            Storage::disk('s3')->assertExists('destino/archivo.txt');
        });

        it('retorna false si origen no existe', function () {
            $service = app(ArchivoService::class);

            expect($service->mover('no-existe.txt', 'destino.txt'))->toBeFalse();
        });
    });

    describe('copiar', function () {
        it('copia archivo manteniendo el original', function () {
            Storage::disk('s3')->put('origen/archivo.txt', 'contenido');
            $service = app(ArchivoService::class);

            $resultado = $service->copiar('origen/archivo.txt', 'copia/archivo.txt');

            expect($resultado)->toBeTrue();
            Storage::disk('s3')->assertExists('origen/archivo.txt');
            Storage::disk('s3')->assertExists('copia/archivo.txt');
        });
    });

    describe('listar', function () {
        it('lista archivos en directorio', function () {
            Storage::disk('s3')->put('empresa/mod/1/a.txt', 'a');
            Storage::disk('s3')->put('empresa/mod/1/b.pdf', 'b');
            $service = app(ArchivoService::class);

            $archivos = $service->listar('empresa/mod/1');

            expect($archivos)->toHaveCount(2);
        });
    });

    describe('eliminarDirectorio', function () {
        it('elimina directorio con todos sus archivos', function () {
            Storage::disk('s3')->put('empresa/mod/1/a.txt', 'a');
            Storage::disk('s3')->put('empresa/mod/1/b.pdf', 'b');
            $service = app(ArchivoService::class);

            $service->eliminarDirectorio('empresa/mod/1');

            Storage::disk('s3')->assertMissing('empresa/mod/1/a.txt');
            Storage::disk('s3')->assertMissing('empresa/mod/1/b.pdf');
        });
    });

    describe('construirDirectorio', function () {
        it('construye ruta con estructura empresa/modulo/id', function () {
            $service = app(ArchivoService::class);

            expect($service->construirDirectorio(1, 'candidatos', 123))
                ->toBe('companies/1/candidatos/123');
        });
    });

    describe('construirRuta', function () {
        it('construye ruta completa con archivo', function () {
            $service = app(ArchivoService::class);

            expect($service->construirRuta(1, 'candidatos', 123, 'cv.pdf'))
                ->toBe('companies/1/candidatos/123/cv.pdf');
        });
    });

    describe('resolverEmpresaId', function () {
        it('resuelve ID desde modelo Empresa', function () {
            $empresa = Empresa::factory()->create(['nombre' => 'Mi Empresa SA de CV']);
            $service = app(ArchivoService::class);

            $ruta = $service->construirDirectorio(
                (new \ReflectionMethod($service, 'resolverEmpresaId'))->invoke($service, $empresa),
                'test',
                1,
            );

            expect($ruta)->toBe("companies/{$empresa->id}/test/1");
        });

        it('resuelve ID desde entero', function () {
            $service = app(ArchivoService::class);

            $id = (new \ReflectionMethod($service, 'resolverEmpresaId'))->invoke($service, 42);

            expect($id)->toBe('42');
        });

        it('usa string directamente como identificador', function () {
            $service = app(ArchivoService::class);

            $id = (new \ReflectionMethod($service, 'resolverEmpresaId'))->invoke($service, '99');

            expect($id)->toBe('99');
        });
    });

    describe('url', function () {
        it('retorna string vacío si archivo no existe', function () {
            $service = app(ArchivoService::class);

            expect($service->url('no-existe.txt'))->toBe('');
        });
    });

    describe('descargar', function () {
        it('retorna StreamedResponse con nombre correcto', function () {
            Storage::disk('s3')->put('test/doc.pdf', 'contenido');
            $service = app(ArchivoService::class);

            $response = $service->descargar('test/doc.pdf', 'mi-documento.pdf');

            expect($response->getStatusCode())->toBe(200);
        });
    });
});
