<?php

declare(strict_types=1);

use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Services\OneSignal\OneSignalService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);

    $this->configuracionApp = ConfiguracionApp::create([
        'nombre_app' => 'Test App',
        'android_app_id' => 'com.test.app',
        'ios_app_id' => 'com.test.app',
        'one_signal_app_id' => 'test-onesignal-app-id',
        'one_signal_rest_api_key' => 'test-rest-api-key',
        'android_channel_id' => 'test-channel-id',
    ]);

    $this->empresa = crearEmpresaMinima([
        'configuracion_app_id' => $this->configuracionApp->id,
    ]);
});

test('empresa puede obtener credenciales de OneSignal', function (): void {
    $credentials = $this->empresa->getOneSignalCredentials();

    expect($credentials)->toBeArray()
        ->and($credentials['app_id'])->toBe('test-onesignal-app-id')
        ->and($credentials['rest_api_key'])->toBe('test-rest-api-key')
        ->and($credentials['android_channel_id'])->toBe('test-channel-id');
});

test('empresa sin configuracion retorna null', function (): void {
    $empresaSinConfig = crearEmpresaMinima([
        'configuracion_app_id' => null,
    ]);

    expect($empresaSinConfig->getOneSignalCredentials())->toBeNull();
});

test('servicio detecta si OneSignal está configurado', function (): void {
    $service = app(OneSignalService::class);

    expect($service->paraEmpresa($this->empresa)->estaConfigurado())->toBeTrue();
});

test('servicio en modo simulación no envía realmente', function (): void {
    $service = app(OneSignalService::class);

    $resultado = $service
        ->paraEmpresa($this->empresa)
        ->simular()
        ->enviarATokens(
            ['token-1', 'token-2'],
            'Título de prueba',
            'Mensaje de prueba',
            ['type' => 'TEST']
        );

    expect($resultado)->toBeArray()
        ->and($resultado['simulado'])->toBeTrue()
        ->and($resultado['recipients'])->toBe(2);
});

test('envío masivo divide en chunks de 2000', function (): void {
    $service = app(OneSignalService::class);

    $tokens = array_map(fn (int $i): string => "token-$i", range(1, 4500));

    $resultados = $service
        ->paraEmpresa($this->empresa)
        ->simular()
        ->enviarMasivo($tokens, 'Título', 'Mensaje');

    expect($resultados)->toHaveCount(3)
        ->and($resultados[0]['tokens'])->toBe(2000)
        ->and($resultados[1]['tokens'])->toBe(2000)
        ->and($resultados[2]['tokens'])->toBe(500);
});

test('empresa carga relacion configuracionApp', function (): void {
    $empresa = Empresa::query()->with('configuracionApp')->findOrFail($this->empresa->id);

    expect($empresa->configuracionApp)->toBeInstanceOf(ConfiguracionApp::class)
        ->and($empresa->configuracionApp->id)->toBe($this->configuracionApp->id);
});
