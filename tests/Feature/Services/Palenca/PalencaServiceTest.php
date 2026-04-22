<?php

declare(strict_types=1);

use App\Services\Palenca\PalencaService;
use Illuminate\Support\Facades\Http;

test('crea consent correctamente', function (): void {
    config([
        'services.palenca.url' => 'https://api.test.palenca.com',
        'services.palenca.key' => 'test-private-key',
    ]);

    Http::fake([
        'api.test.palenca.com/consents' => Http::response([
            'id' => 'consent-abc-123',
            'status' => 'created',
        ], 200),
    ]);

    $service = new PalencaService;
    $resultado = $service->crearConsent('GARJ900115HDFRRL09');

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['consent_id'])->toBe('consent-abc-123');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/consents')
        && $request->hasHeader('x-api-key', 'test-private-key')
        && $request['identifier'] === 'GARJ900115HDFRRL09'
        && isset($request['ip_address'])
        && isset($request['privacy_notice_url']));
});

test('crea verification correctamente', function (): void {
    config([
        'services.palenca.url' => 'https://api.test.palenca.com',
        'services.palenca.key' => 'test-private-key',
    ]);

    Http::fake([
        'api.test.palenca.com/verifications' => Http::response([
            'id' => 'verif-xyz-789',
            'status' => 'pending',
        ], 200),
    ]);

    $service = new PalencaService;
    $resultado = $service->crearVerification('GARJ900115HDFRRL09');

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['verification_id'])->toBe('verif-xyz-789');

    Http::assertSent(fn ($request) => $request['identifier'] === 'GARJ900115HDFRRL09'
        && ! isset($request['consent_id']));
});

test('obtiene perfil correctamente', function (): void {
    config([
        'services.palenca.url' => 'https://api.test.palenca.com',
        'services.palenca.key' => 'test-private-key',
    ]);

    Http::fake([
        'api.test.palenca.com/profile/GARJ900115HDFRRL09' => Http::response([
            'personal_info' => [
                'first_name' => 'Juan',
                'last_name' => 'García',
                'nss' => '12345678901',
            ],
            'employment_status' => 'EMPLEADO',
        ], 200),
    ]);

    $service = new PalencaService;
    $resultado = $service->obtenerPerfil('GARJ900115HDFRRL09');

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['nss'])->toBe('12345678901')
        ->and($resultado['nombre_imss'])->toBe('Juan García')
        ->and($resultado['estatus_laboral'])->toBe('EMPLEADO');
});

test('obtiene empleos correctamente', function (): void {
    config([
        'services.palenca.url' => 'https://api.test.palenca.com',
        'services.palenca.key' => 'test-private-key',
    ]);

    Http::fake([
        'api.test.palenca.com/employments/GARJ900115HDFRRL09' => Http::response([
            'semanas_cotizadas' => 520,
            'employment_history' => [
                ['employer' => 'Empresa A', 'start_date' => '2020-01-01'],
                ['employer' => 'Empresa B', 'start_date' => '2018-06-15'],
            ],
        ], 200),
    ]);

    $service = new PalencaService;
    $resultado = $service->obtenerEmpleos('GARJ900115HDFRRL09');

    expect($resultado['success'])->toBeTrue()
        ->and($resultado['semanas_cotizadas'])->toBe(520)
        ->and($resultado['empleos'])->toHaveCount(2);
});

test('retorna error si no configurado', function (): void {
    config([
        'services.palenca.url' => '',
        'services.palenca.key' => '',
    ]);

    $service = new PalencaService;

    expect($service->estaConfigurado())->toBeFalse();

    $resultado = $service->crearConsent('GARJ900115HDFRRL09');
    expect($resultado['success'])->toBeFalse()
        ->and($resultado['error'])->toContain('no configurado');
});

test('maneja error http en consent', function (): void {
    config([
        'services.palenca.url' => 'https://api.test.palenca.com',
        'services.palenca.key' => 'test-key',
    ]);

    Http::fake([
        'api.test.palenca.com/consents' => Http::response(['error' => 'bad request'], 400),
    ]);

    $service = new PalencaService;
    $resultado = $service->crearConsent('CURP_INVALIDO');

    expect($resultado['success'])->toBeFalse()
        ->and($resultado['error'])->toContain('400');
});
