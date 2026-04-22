<?php

/**
 * Correo de bienvenida al colaborador: se envía solo si tiene email real y la empresa está activa.
 */

use App\Mail\BienvenidaColaboradorMail;
use App\Services\ColaboradorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima(['activo' => true]);
    $this->service = app(ColaboradorService::class);
});

function datosColaboradorParaMail(array $overrides = []): array
{
    return array_merge([
        'nombre' => 'Laura',
        'apellido_paterno' => 'Martínez',
        'apellido_materno' => 'López',
        'email' => 'laura.martinez.'.uniqid().'@empresa.com',
        'fecha_nacimiento' => '1992-03-20',
        'fecha_ingreso' => '2024-06-01',
        'periodicidad_pago' => 'QUINCENAL',
    ], $overrides);
}

it('envía correo de bienvenida cuando el colaborador tiene email real', function (): void {
    Mail::fake();

    $data = datosColaboradorParaMail();
    $this->service->crearColaborador($data, $this->empresa);

    Mail::assertQueued(BienvenidaColaboradorMail::class, function (BienvenidaColaboradorMail $mail) use ($data): bool {
        return $mail->hasTo($data['email'])
            && $mail->colaborador->email === $data['email'];
    });
});

it('no envía correo cuando el email es placeholder (solo teléfono)', function (): void {
    Mail::fake();

    $data = datosColaboradorParaMail([
        'email' => null,
        'telefono_movil' => '5512345678',
    ]);
    $this->service->crearColaborador($data, $this->empresa);

    Mail::assertNotQueued(BienvenidaColaboradorMail::class);
});

it('no envía correo cuando la empresa está inactiva', function (): void {
    Mail::fake();

    $this->empresa->update(['activo' => false]);

    $data = datosColaboradorParaMail();
    $this->service->crearColaborador($data, $this->empresa);

    Mail::assertNotQueued(BienvenidaColaboradorMail::class);
});

it('el correo contiene los datos correctos del colaborador y empresa', function (): void {
    Mail::fake();

    $this->empresa->update(['nombre_app' => 'Mi App Empresa']);

    $data = datosColaboradorParaMail(['nombre' => 'Carlos', 'apellido_paterno' => 'Ruiz', 'apellido_materno' => 'Soto']);
    $this->service->crearColaborador($data, $this->empresa);

    Mail::assertQueued(BienvenidaColaboradorMail::class, function (BienvenidaColaboradorMail $mail): bool {
        return $mail->nombreCompleto === 'Carlos Ruiz Soto'
            && $mail->empresaNombre === $this->empresa->nombre
            && $mail->nombreApp === 'Mi App Empresa';
    });
});

it('usa nombre Paco por defecto si la empresa no tiene nombre_app', function (): void {
    Mail::fake();

    $this->empresa->update(['nombre_app' => null]);

    $data = datosColaboradorParaMail();
    $this->service->crearColaborador($data, $this->empresa);

    Mail::assertQueued(BienvenidaColaboradorMail::class, function (BienvenidaColaboradorMail $mail): bool {
        return $mail->nombreApp === 'Paco';
    });
});

it('el fallo del correo no afecta la creación del colaborador', function (): void {
    Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

    $data = datosColaboradorParaMail();
    $user = $this->service->crearColaborador($data, $this->empresa);

    expect($user)->not->toBeNull()
        ->and($user->colaborador)->not->toBeNull()
        ->and($user->email)->toBe($data['email']);
});

it('el mailable renderiza sin errores', function (): void {
    $empresa = crearEmpresaMinima(['activo' => true, 'nombre_app' => 'TestApp', 'logo_url' => 'https://example.com/logo.png']);
    $user = crearUserColaborador($empresa);
    $colaborador = $user->colaborador;

    $mail = new BienvenidaColaboradorMail($colaborador, $empresa);
    $rendered = $mail->render();

    expect($rendered)
        ->toContain('¡Bienvenido/a')
        ->toContain($colaborador->nombre_completo)
        ->toContain($empresa->nombre)
        ->toContain('TestApp')
        ->toContain('Descargar la App');
});
