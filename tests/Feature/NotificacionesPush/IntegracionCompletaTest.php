<?php

declare(strict_types=1);

use App\Enums\EstadoNotificacionPush;
use App\Filament\Resources\NotificacionesPush\NotificacionPushResource;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\NotificacionPush;
use App\Models\NotificacionPushDestinatario;
use App\Models\User;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

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

    $permisos = [
        'ViewAny:NotificacionPush',
        'View:NotificacionPush',
        'Create:NotificacionPush',
        'Update:NotificacionPush',
        'Delete:NotificacionPush',
        'DeleteAny:NotificacionPush',
    ];
    foreach ($permisos as $nombre) {
        Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
    }

    $this->admin = User::factory()->administrador()->create([
        'email' => fake()->unique()->safeEmail(),
    ]);
    $this->admin->givePermissionTo($permisos);

    $this->colaborador1 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $this->user1 = User::factory()->colaborador()->create([
        'colaborador_id' => $this->colaborador1->id,
        'empresa_id' => $this->empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    $this->colaborador2 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
    $this->user2 = User::factory()->colaborador()->create([
        'colaborador_id' => $this->colaborador2->id,
        'empresa_id' => $this->empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);
});

describe('Flujo completo de creación y envío', function (): void {

    test('crear notificación → persistir destinatarios → enviar', function (): void {
        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Notificación de prueba',
            'mensaje' => 'Este es el mensaje de prueba',
            'estado' => EstadoNotificacionPush::BORRADOR,
            'filtros' => [
                'destinatarios' => [
                    'select_all' => true,
                    'manual_activation' => [],
                    'manual_deactivation' => [],
                ],
            ],
            'creado_por' => $this->admin->id,
        ]);

        $resolverService = app(ResolverDestinatariosService::class);
        $total = $resolverService->persistirDestinatarios($notificacion);

        expect($total)->toBe(2)
            ->and($notificacion->fresh()->destinatarios)->toHaveCount(2);

        $notificacion->update(['estado' => EstadoNotificacionPush::ENVIANDO]);
        EnviarNotificacionPushJob::dispatch($notificacion->fresh());

        Queue::assertPushed(EnviarNotificacionPushJob::class, function (EnviarNotificacionPushJob $job) use ($notificacion): bool {
            return $job->notificacion->id === $notificacion->id;
        });
    });

    test('crear notificación con selección manual parcial', function (): void {
        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Solo para colaborador 1',
            'mensaje' => 'Mensaje selectivo',
            'estado' => EstadoNotificacionPush::BORRADOR,
            'filtros' => [
                'destinatarios' => [
                    'select_all' => false,
                    'manual_activation' => [$this->colaborador1->id],
                    'manual_deactivation' => [],
                ],
            ],
            'creado_por' => $this->admin->id,
        ]);

        $resolverService = app(ResolverDestinatariosService::class);
        $total = $resolverService->persistirDestinatarios($notificacion);

        expect($total)->toBe(1);

        $notificacion->refresh()->load('destinatarios');
        $destIds = $notificacion->destinatarios->pluck('user_id')->toArray();

        expect($destIds)->toContain($this->user1->id)
            ->and($destIds)->not->toContain($this->user2->id);
    });

    test('crear notificación con exclusión manual', function (): void {
        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Todos menos colaborador 2',
            'mensaje' => 'Mensaje con exclusión',
            'estado' => EstadoNotificacionPush::BORRADOR,
            'filtros' => [
                'destinatarios' => [
                    'select_all' => true,
                    'manual_activation' => [],
                    'manual_deactivation' => [$this->colaborador2->id],
                ],
            ],
            'creado_por' => $this->admin->id,
        ]);

        $resolverService = app(ResolverDestinatariosService::class);
        $total = $resolverService->persistirDestinatarios($notificacion);

        expect($total)->toBe(1);

        $notificacion->refresh()->load('destinatarios');
        $destIds = $notificacion->destinatarios->pluck('user_id')->toArray();

        expect($destIds)->toContain($this->user1->id)
            ->and($destIds)->not->toContain($this->user2->id);
    });
});

describe('Flujo de notificación programada', function (): void {

    test('comando procesa notificación programada y recalcula destinatarios', function (): void {
        $notificacion = NotificacionPush::query()->create([
            'empresa_id' => $this->empresa->id,
            'titulo' => 'Notificación programada',
            'mensaje' => 'Mensaje programado',
            'estado' => EstadoNotificacionPush::PROGRAMADA,
            'programada_para' => now()->subHour(),
            'filtros' => [
                'destinatarios' => ['select_all' => true],
            ],
            'creado_por' => $this->admin->id,
        ]);

        $resolverService = app(ResolverDestinatariosService::class);
        $resolverService->persistirDestinatarios($notificacion);

        expect($notificacion->fresh()->destinatarios)->toHaveCount(2);

        $colaborador3 = Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);
        User::factory()->colaborador()->create([
            'colaborador_id' => $colaborador3->id,
            'empresa_id' => $this->empresa->id,
            'email' => fake()->unique()->safeEmail(),
        ]);

        $this->artisan('notificaciones:enviar-programadas')
            ->assertSuccessful();

        expect($notificacion->fresh()->destinatarios)->toHaveCount(3);

        Queue::assertPushed(EnviarNotificacionPushJob::class);
    });
});

describe('API para app móvil', function (): void {

    test('colaborador puede ver sus notificaciones', function (): void {
        $notificacion = NotificacionPush::factory()->enviada()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        NotificacionPushDestinatario::query()->create([
            'notificacion_push_id' => $notificacion->id,
            'user_id' => $this->user1->id,
            'enviado' => true,
            'estado_lectura' => 'NO_LEIDA',
        ]);

        Sanctum::actingAs($this->user1);

        $this->getJson('/api/notificaciones-push')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $notificacion->id);
    });

    test('colaborador puede marcar notificación como leída', function (): void {
        $notificacion = NotificacionPush::factory()->enviada()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $destinatario = NotificacionPushDestinatario::query()->create([
            'notificacion_push_id' => $notificacion->id,
            'user_id' => $this->user1->id,
            'enviado' => true,
            'estado_lectura' => 'NO_LEIDA',
        ]);

        Sanctum::actingAs($this->user1);

        $this->postJson("/api/notificaciones-push/{$notificacion->id}/leer")
            ->assertOk();

        expect($destinatario->fresh()->estado_lectura)->toBe('LEIDA');
    });

    test('estadísticas de lectura se actualizan correctamente', function (): void {
        $notificacion = NotificacionPush::factory()->enviada()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        NotificacionPushDestinatario::query()->create([
            'notificacion_push_id' => $notificacion->id,
            'user_id' => $this->user1->id,
            'enviado' => true,
            'estado_lectura' => 'LEIDA',
        ]);

        NotificacionPushDestinatario::query()->create([
            'notificacion_push_id' => $notificacion->id,
            'user_id' => $this->user2->id,
            'enviado' => true,
            'estado_lectura' => 'NO_LEIDA',
        ]);

        $stats = $notificacion->fresh()->getEstadisticasLectura();

        expect($stats['total_destinatarios'])->toBe(2)
            ->and($stats['leidas'])->toBe(1)
            ->and($stats['no_leidas'])->toBe(1);

        expect((float) $stats['porcentaje_lectura'])->toEqualWithDelta(50.0, 0.01);
    });
});

describe('Panel Admin', function (): void {

    test('admin puede acceder al listado', function (): void {
        $this->actingAs($this->admin)
            ->get(NotificacionPushResource::getUrl('index', panel: 'admin'))
            ->assertOk();
    });

    test('admin puede crear notificación', function (): void {
        $this->actingAs($this->admin)
            ->get(NotificacionPushResource::getUrl('create', panel: 'admin'))
            ->assertOk()
            ->assertSee('Empresa', false);
    });

    test('listado muestra columna de empresa', function (): void {
        NotificacionPush::factory()->create([
            'empresa_id' => $this->empresa->id,
        ]);

        $this->actingAs($this->admin)
            ->get(NotificacionPushResource::getUrl('index', panel: 'admin'))
            ->assertOk()
            ->assertSee($this->empresa->nombre, false);
    });
});
