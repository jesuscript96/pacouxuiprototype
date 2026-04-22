<?php

declare(strict_types=1);

use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\NotificacionPush;
use App\Models\User;
use App\Services\NotificacionesPush\ObtenerDestinatariosReal;
use Carbon\Carbon;
use Database\Seeders\Inicial;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(Inicial::class);
    $this->empresa = crearEmpresaMinima();
    $this->servicio = new ObtenerDestinatariosReal;
});

function crearColaboradorConUsuario(Empresa $empresa, array $overrides = []): Colaborador
{
    $data = array_merge(['empresa_id' => $empresa->id], $overrides);

    if (isset($data['fecha_nacimiento']) && $data['fecha_nacimiento'] instanceof Carbon) {
        $data['fecha_nacimiento'] = $data['fecha_nacimiento']->format('Y-m-d');
    }
    if (isset($data['fecha_ingreso']) && $data['fecha_ingreso'] instanceof Carbon) {
        $data['fecha_ingreso'] = $data['fecha_ingreso']->format('Y-m-d');
    }

    $colaborador = Colaborador::factory()->create($data);

    User::factory()->colaborador()->create([
        'colaborador_id' => $colaborador->id,
        'empresa_id' => $empresa->id,
        'email' => fake()->unique()->safeEmail(),
    ]);

    return $colaborador;
}

test('retorna colaboradores de la empresa sin filtros', function (): void {
    $colaborador1 = crearColaboradorConUsuario($this->empresa);
    $colaborador2 = crearColaboradorConUsuario($this->empresa);

    Colaborador::factory()->create(['empresa_id' => $this->empresa->id]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(2)
        ->and($resultado->pluck('id')->sort()->values()->all())->toBe(
            collect([$colaborador1->id, $colaborador2->id])->sort()->values()->all()
        );
});

test('filtra por ubicación', function (): void {
    $ubicacion1 = crearUbicacion($this->empresa, ['nombre' => 'U1']);
    $ubicacion2 = crearUbicacion($this->empresa, ['nombre' => 'U2', 'cp' => '01002']);

    $colaborador1 = crearColaboradorConUsuario($this->empresa, ['ubicacion_id' => $ubicacion1->id]);
    crearColaboradorConUsuario($this->empresa, ['ubicacion_id' => $ubicacion2->id]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['ubicaciones' => [$ubicacion1->id]],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaborador1->id);
});

test('filtra por departamento', function (): void {
    $departamento1 = Departamento::query()->create([
        'empresa_id' => $this->empresa->id,
        'nombre' => 'Departamento A',
    ]);
    $departamento2 = Departamento::query()->create([
        'empresa_id' => $this->empresa->id,
        'nombre' => 'Departamento B',
    ]);

    $colaborador1 = crearColaboradorConUsuario($this->empresa, ['departamento_id' => $departamento1->id]);
    crearColaboradorConUsuario($this->empresa, ['departamento_id' => $departamento2->id]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['departamentos' => [$departamento1->id]],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaborador1->id);
});

test('filtra por género', function (): void {
    crearColaboradorConUsuario($this->empresa, ['genero' => 'M']);
    $colaboradorF = crearColaboradorConUsuario($this->empresa, ['genero' => 'F']);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['generos' => ['F']],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaboradorF->id);
});

test('filtra por edad mínima', function (): void {
    $colaborador30 = crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => now()->subYears(30),
    ]);

    crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => now()->subYears(20),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['edad_minima' => 25],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaborador30->id);
});

test('filtra por edad máxima', function (): void {
    crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => now()->subYears(30),
    ]);

    $colaborador20 = crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => now()->subYears(20),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['edad_maxima' => 25],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaborador20->id);
});

test('filtra por antigüedad mínima en meses', function (): void {
    $colaboradorAntiguo = crearColaboradorConUsuario($this->empresa, [
        'fecha_ingreso' => now()->subMonths(24),
    ]);

    crearColaboradorConUsuario($this->empresa, [
        'fecha_ingreso' => now()->subMonths(6),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['antiguedad_minima_meses' => 12],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaboradorAntiguo->id);
});

test('filtra por cumpleañeros del mes', function (): void {
    $colaboradorMarzo = crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => Carbon::create(2000, 3, 15),
    ]);

    crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => Carbon::create(2000, 7, 15),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['cumpleaneros_mes' => [3]],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaboradorMarzo->id);
});

test('cumpleaneros_mes acepta un solo mes escalar', function (): void {
    $colaboradorMarzo = crearColaboradorConUsuario($this->empresa, [
        'fecha_nacimiento' => Carbon::create(2000, 3, 15),
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => ['cumpleaneros_mes' => 3],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaboradorMarzo->id);
});

test('combina múltiples filtros', function (): void {
    $ubicacion = crearUbicacion($this->empresa, ['nombre' => 'U comb', 'cp' => '01003']);

    $colaboradorMatch = crearColaboradorConUsuario($this->empresa, [
        'ubicacion_id' => $ubicacion->id,
        'genero' => 'F',
    ]);

    crearColaboradorConUsuario($this->empresa, [
        'ubicacion_id' => $ubicacion->id,
        'genero' => 'M',
    ]);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [
            'ubicaciones' => [$ubicacion->id],
            'generos' => ['F'],
        ],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresFiltrados($notificacion);

    expect($resultado)->toHaveCount(1)
        ->and($resultado->first()->id)->toBe($colaboradorMatch->id);
});

test('contarDestinatarios retorna el conteo correcto', function (): void {
    crearColaboradorConUsuario($this->empresa);
    crearColaboradorConUsuario($this->empresa);
    crearColaboradorConUsuario($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [],
    ]);

    $conteo = $this->servicio->contarDestinatarios($notificacion);

    expect($conteo)->toBe(3);
});

test('obtenerColaboradoresPaginados pagina correctamente', function (): void {
    for ($i = 0; $i < 15; $i++) {
        crearColaboradorConUsuario($this->empresa);
    }

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [],
    ]);

    $pagina1 = $this->servicio->obtenerColaboradoresPaginados($notificacion, perPage: 10);

    expect($pagina1->total())->toBe(15)
        ->and($pagina1->count())->toBe(10)
        ->and($pagina1->lastPage())->toBe(2);
});

test('obtenerColaboradoresPaginados filtra por búsqueda', function (): void {
    crearColaboradorConUsuario($this->empresa, ['nombre' => 'Juan', 'apellido_paterno' => 'Pérez']);
    crearColaboradorConUsuario($this->empresa, ['nombre' => 'María', 'apellido_paterno' => 'García']);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [],
    ]);

    $resultado = $this->servicio->obtenerColaboradoresPaginados($notificacion, busqueda: 'Juan');

    expect($resultado->total())->toBe(1)
        ->and($resultado->first()->nombre)->toBe('Juan');
});

test('obtenerTokens retorna un placeholder por colaborador', function (): void {
    $c = crearColaboradorConUsuario($this->empresa);

    $notificacion = NotificacionPush::factory()->create([
        'empresa_id' => $this->empresa->id,
        'filtros' => [],
    ]);

    $tokens = $this->servicio->obtenerTokens($notificacion);

    expect($tokens)->toHaveCount(1)
        ->and($tokens->first())->toBe('colaborador-'.$c->id.'-placeholder');
});
