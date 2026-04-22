<?php

declare(strict_types=1);

use App\Enums\EstadoVerificacionCuenta;
use App\Filament\Resources\VerificacionCuentas\Pages\ListVerificacionCuentas;
use App\Filament\Resources\VerificacionCuentas\VerificacionCuentaResource;
use App\Filament\Resources\VerificacionCuentas\Widgets\ContadorCuentasPendientesWidget;
use App\Jobs\EnviarNotificacionPushJob;
use App\Models\Banco;
use App\Models\Colaborador;
use App\Models\ConfiguracionApp;
use App\Models\CuentaBancaria;
use App\Models\NotificacionPush;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);

    Queue::fake();

    $this->empresa = crearEmpresaMinima();

    $this->banco = Banco::query()->create([
        'nombre' => 'Banco Verificación Test',
        'codigo' => fake()->unique()->numberBetween(100, 999),
        'comision' => 0.00,
    ]);

    $this->colaborador = Colaborador::factory()->create([
        'empresa_id' => $this->empresa->id,
        'fecha_ingreso' => now()->subMonths(6),
    ]);

    $configuracionApp = ConfiguracionApp::query()->create([
        'nombre_app' => 'App Test Verificación',
        'android_app_id' => 'com.test.verificacion',
        'ios_app_id' => 'com.test.verificacion',
        'one_signal_app_id' => 'test-onesignal-app-id',
        'one_signal_rest_api_key' => 'test-onesignal-rest-api-key',
    ]);
    $this->empresa->update(['configuracion_app_id' => $configuracionApp->id]);

    $this->empresa->notificacionesIncluidas()->attach([2, 3]);

    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->adminUser = User::factory()->administrador()->create([
        'email' => fake()->unique()->safeEmail(),
    ]);
    $this->adminUser->assignRole($superAdminRole);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->adminUser);
});

function crearCuentaVerificacion(array $overrides = []): CuentaBancaria
{
    return CuentaBancaria::query()->create(array_merge([
        'numero' => (string) fake()->unique()->numberBetween(10000000, 99999999),
        'banco_id' => test()->banco->id,
        'colaborador_id' => test()->colaborador->id,
        'user_id' => test()->adminUser->id,
        'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
    ], $overrides));
}

// =====================
// Acceso al Resource
// =====================

describe('Acceso al Resource', function (): void {

    test('super_admin puede acceder al listado', function (): void {
        $url = VerificacionCuentaResource::getUrl('index', panel: 'admin');

        $this->get($url)->assertOk();
    });

    test('usuario sin permisos no puede acceder', function (): void {
        $userSinPermisos = User::factory()->administrador()->create([
            'email' => fake()->unique()->safeEmail(),
        ]);

        $this->actingAs($userSinPermisos);

        $url = VerificacionCuentaResource::getUrl('index', panel: 'admin');

        $this->get($url)->assertForbidden();
    });

    test('canViewAny retorna false para usuario sin rol ni permiso', function (): void {
        $userComun = User::factory()->administrador()->create([
            'email' => fake()->unique()->safeEmail(),
        ]);
        $this->actingAs($userComun);

        expect(VerificacionCuentaResource::canViewAny())->toBeFalse();
    });

});

// =====================
// Listado de cuentas
// =====================

describe('Listado de cuentas', function (): void {

    test('muestra cuentas bancarias en la tabla', function (): void {
        $cuentaSinVerificar = crearCuentaVerificacion([
            'numero' => '1111111111',
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $cuentaVerificada = crearCuentaVerificacion([
            'numero' => '2222222222',
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertCanSeeTableRecords([$cuentaSinVerificar, $cuentaVerificada]);
    });

    test('filtra por estado correctamente', function (): void {
        $cuentaSinVerificar = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $cuentaVerificada = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->filterTable('estado', EstadoVerificacionCuenta::SIN_VERIFICAR->value)
            ->assertCanSeeTableRecords([$cuentaSinVerificar])
            ->assertCanNotSeeTableRecords([$cuentaVerificada]);
    });

    test('ordena por fecha de creación descendente por defecto', function (): void {
        Livewire::test(ListVerificacionCuentas::class)
            ->assertSuccessful();
    });

});

// =====================
// Acción Validar
// =====================

describe('Acción Validar', function (): void {

    test('valida cuenta desde acción de tabla', function (): void {
        $cuenta = crearCuentaVerificacion([
            'numero' => '1234567890',
            'es_nomina' => false,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('validar', $cuenta);

        $cuenta->refresh();
        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::VERIFICADA)
            ->and($cuenta->es_nomina)->toBeTrue();

        Queue::assertPushed(EnviarNotificacionPushJob::class);
    });

    test('validar crea notificación push con datos correctos', function (): void {
        $cuenta = crearCuentaVerificacion([
            'numero' => '9876543210',
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('validar', $cuenta);

        $notificacion = NotificacionPush::query()
            ->where('empresa_id', $this->empresa->id)
            ->first();

        expect($notificacion)->not->toBeNull()
            ->and($notificacion->titulo)->toBe('Validación de cuenta EXITOSA')
            ->and($notificacion->mensaje)->toContain('3210');
    });

    test('acción validar no visible en cuenta ya verificada', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertTableActionHidden('validar', $cuenta);
    });

    test('acción validar no visible en cuenta rechazada', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertTableActionHidden('validar', $cuenta);
    });

});

// =====================
// Acción Rechazar
// =====================

describe('Acción Rechazar', function (): void {

    test('rechaza cuenta desde acción de tabla', function (): void {
        $cuenta = crearCuentaVerificacion([
            'es_nomina' => true,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('rechazar', $cuenta);

        $cuenta->refresh();
        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::RECHAZADA);

        Queue::assertPushed(EnviarNotificacionPushJob::class);
    });

    test('rechazar cuenta no-nómina la elimina (soft delete)', function (): void {
        $cuenta = crearCuentaVerificacion([
            'es_nomina' => false,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('rechazar', $cuenta);

        expect(CuentaBancaria::find($cuenta->id))->toBeNull()
            ->and(CuentaBancaria::withTrashed()->find($cuenta->id))->not->toBeNull();
    });

    test('acción rechazar no visible en cuenta verificada', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertTableActionHidden('rechazar', $cuenta);
    });

    test('acción rechazar no visible en cuenta rechazada', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertTableActionHidden('rechazar', $cuenta);
    });

});

// =====================
// Acción Reenviar
// =====================

describe('Acción Reenviar', function (): void {

    test('reenvía cuenta rechazada a verificación', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
            'enviado_verificacion' => true,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('reenviar', $cuenta);

        $cuenta->refresh();
        expect($cuenta->estado)->toBe(EstadoVerificacionCuenta::SIN_VERIFICAR)
            ->and($cuenta->enviado_verificacion)->toBeFalse();
    });

    test('acción reenviar solo visible en cuentas rechazadas', function (): void {
        $cuentaSinVerificar = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::SIN_VERIFICAR,
        ]);

        $cuentaRechazada = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);

        $cuentaVerificada = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::VERIFICADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->assertTableActionHidden('reenviar', $cuentaSinVerificar)
            ->assertTableActionVisible('reenviar', $cuentaRechazada)
            ->assertTableActionHidden('reenviar', $cuentaVerificada);
    });

    test('reenviar no dispara notificación push', function (): void {
        $cuenta = crearCuentaVerificacion([
            'estado' => EstadoVerificacionCuenta::RECHAZADA,
        ]);

        Livewire::test(ListVerificacionCuentas::class)
            ->callTableAction('reenviar', $cuenta);

        expect(NotificacionPush::query()->count())->toBe(0);
        Queue::assertNotPushed(EnviarNotificacionPushJob::class);
    });

});

// =====================
// Widget de contadores
// =====================

describe('Widget de contadores', function (): void {

    test('widget se muestra en la página del listado', function (): void {
        $url = VerificacionCuentaResource::getUrl('index', panel: 'admin');

        $this->get($url)->assertOk()->assertSeeLivewire(ContadorCuentasPendientesWidget::class);
    });

    test('widget muestra contadores por estado', function (): void {
        crearCuentaVerificacion(['estado' => EstadoVerificacionCuenta::SIN_VERIFICAR, 'enviado_verificacion' => false]);
        crearCuentaVerificacion(['estado' => EstadoVerificacionCuenta::SIN_VERIFICAR, 'enviado_verificacion' => true]);
        crearCuentaVerificacion(['estado' => EstadoVerificacionCuenta::VERIFICADA]);
        crearCuentaVerificacion(['estado' => EstadoVerificacionCuenta::RECHAZADA]);

        Livewire::test(ContadorCuentasPendientesWidget::class)
            ->assertSee('Sin verificar')
            ->assertSee('Pendientes de envío')
            ->assertSee('Verificadas')
            ->assertSee('Rechazadas');
    });

});

// =====================
// Acciones de header
// =====================

describe('Acciones de header', function (): void {

    test('exportar CSV está disponible como acción', function (): void {
        Livewire::test(ListVerificacionCuentas::class)
            ->assertActionExists('exportarCsv');
    });

    test('generar TXT está disponible como acción', function (): void {
        Livewire::test(ListVerificacionCuentas::class)
            ->assertActionExists('exportarTxt');
    });

    test('cargar resultados está disponible como acción', function (): void {
        Livewire::test(ListVerificacionCuentas::class)
            ->assertActionExists('cargarResultados');
    });

});
