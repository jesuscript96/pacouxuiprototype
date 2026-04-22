<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/../Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->empresa = crearEmpresaMinima();
});

it('crea relación user–empresa en pivot sin atributos de tipo', function (): void {
    $user = User::factory()->colaborador()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    expect(
        DB::table('empresa_user')
            ->where('user_id', $user->id)
            ->where('empresa_id', $this->empresa->id)
            ->exists()
    )->toBeTrue();
});

it('crea relación user–empresa para usuario cliente', function (): void {
    $user = User::factory()->cliente()->create(['empresa_id' => $this->empresa->id]);
    $user->empresas()->attach($this->empresa->id);

    expect(
        DB::table('empresa_user')
            ->where('user_id', $user->id)
            ->where('empresa_id', $this->empresa->id)
            ->exists()
    )->toBeTrue();
});

it('mismo user y misma empresa solo admiten una fila en empresa_user', function (): void {
    $user = User::factory()->create([
        'tipo' => ['cliente', 'colaborador'],
        'empresa_id' => $this->empresa->id,
        'name' => 'X',
        'apellido_paterno' => 'Y',
        'apellido_materno' => 'Z',
    ]);
    $user->empresas()->attach($this->empresa->id);

    expect(fn () => $user->empresas()->attach($this->empresa->id))
        ->toThrow(QueryException::class);
});

it('al quitar una fila pivot se conservan otras del mismo user', function (): void {
    $e1 = crearEmpresaMinima();
    $e2 = crearEmpresaMinima();

    $user = User::factory()->cliente()->create(['empresa_id' => $e1->id]);
    $user->empresas()->attach($e1->id);
    $user->empresas()->attach($e2->id);

    $user->empresas()->detach($e1->id);

    expect(
        DB::table('empresa_user')->where('user_id', $user->id)->count()
    )->toBe(1)
        ->and(
            (int) DB::table('empresa_user')->where('user_id', $user->id)->value('empresa_id')
        )->toBe($e2->id);
});
