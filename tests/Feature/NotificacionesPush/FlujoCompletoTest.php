<?php

declare(strict_types=1);

use App\Enums\EstadoNotificacionPush;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    Queue::fake();

    $this->configuracionApp = ConfiguracionApp::query()->create([
        'nombre_app' => 'Test App',
        'android_app_id' => 'com.test',
        'ios_app_id' => 'com.test',
        'one_signal_app_id' => 'test-app-id',
        'one_signal_rest_api_key' => 'test-key',
    ]);

    $this->empresa = crearEmpresaMinima();
    $this->empresa->update(['configuracion_app_id' => $this->configuracionApp->id]);
});

test('flujo completo: crear notificación con destinatarios y enviar', function (): void {
    crearColaboradorConUsuarioFlujo($this->empresa);
    crearColaboradorConUsuarioFlujo($this->empresa);

    $notificacion = NotificacionPush::query()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Test',
        'mensaje' => 'Mensaje de prueba',
        'estado' => EstadoNotificacionPush::BORRADOR,
        'filtros' => [
            'destinatarios' => ['select_all' => true],
        ],
        'creado_por' => User::factory()->create()->id,
    ]);

    $resolverService = app(ResolverDestinatariosService::class);
    $total = $resolverService->persistirDestinatarios($notificacion);

    expect($total)->toBe(2)
        ->and($notificacion->fresh()->destinatarios)->toHaveCount(2);

    $notificacion->update(['estado' => EstadoNotificacionPush::ENVIANDO]);
    EnviarNotificacionPushJob::dispatch($notificacion);

    Queue::assertPushed(EnviarNotificacionPushJob::class);
});

test('flujo con selección manual parcial', function (): void {
    $colaborador1 = crearColaboradorConUsuarioFlujo($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioFlujo($this->empresa);
    $colaborador3 = crearColaboradorConUsuarioFlujo($this->empresa);

    $notificacion = NotificacionPush::query()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Test',
        'mensaje' => 'Mensaje',
        'estado' => EstadoNotificacionPush::BORRADOR,
        'filtros' => [
            'destinatarios' => [
                'select_all' => false,
                'manual_activation' => [$colaborador1->id, $colaborador3->id],
            ],
        ],
        'creado_por' => User::factory()->create()->id,
    ]);

    $resolverService = app(ResolverDestinatariosService::class);
    $total = $resolverService->persistirDestinatarios($notificacion);

    expect($total)->toBe(2);

    $notificacion->refresh()->load('destinatarios');
    $destIds = $notificacion->destinatarios->pluck('user_id')->toArray();

    expect($destIds)->toContain($colaborador1->user->id, $colaborador3->user->id)
        ->and($destIds)->not->toContain($colaborador2->user->id);
});

test('flujo con exclusión manual', function (): void {
    $colaborador1 = crearColaboradorConUsuarioFlujo($this->empresa);
    $colaborador2 = crearColaboradorConUsuarioFlujo($this->empresa);
    $colaborador3 = crearColaboradorConUsuarioFlujo($this->empresa);

    $notificacion = NotificacionPush::query()->create([
        'empresa_id' => $this->empresa->id,
        'titulo' => 'Test',
        'mensaje' => 'Mensaje',
        'estado' => EstadoNotificacionPush::BORRADOR,
        'filtros' => [
            'destinatarios' => [
                'select_all' => true,
                'manual_deactivation' => [$colaborador2->id],
            ],
        ],
        'creado_por' => User::factory()->create()->id,
    ]);

    $resolverService = app(ResolverDestinatariosService::class);
    $total = $resolverService->persistirDestinatarios($notificacion);

    expect($total)->toBe(2);

    $notificacion->refresh()->load('destinatarios');
    $destIds = $notificacion->destinatarios->pluck('user_id')->toArray();

    expect($destIds)->toContain($colaborador1->user->id, $colaborador3->user->id)
        ->and($destIds)->not->toContain($colaborador2->user->id);
});

function crearColaboradorConUsuarioFlujo(Empresa $empresa, array $overrides = []): Colaborador
{
    $colaborador = Colaborador::factory()->create(array_merge(
        ['empresa_id' => $empresa->id],
        $overrides
    ));

    User::factory()->colaborador()->create([
        'colaborador_id' => $colaborador->id,
        'empresa_id' => $empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    return $colaborador;
}
