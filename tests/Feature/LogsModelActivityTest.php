<?php

declare(strict_types=1);

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

it('registra actividad en activity_log al actualizar un modelo con LogsModelActivity', function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $empresa = crearEmpresaMinima();
    $user = User::factory()->administrador()->create([
        'email' => 'audit.logs.'.uniqid('', true).'@test.com',
    ]);

    $this->actingAs($user);

    $empresa->update(['nombre' => 'Empresa audit '.uniqid()]);

    expect(
        DB::table('activity_log')
            ->where('subject_type', Empresa::class)
            ->where('subject_id', $empresa->id)
            ->where('causer_id', $user->id)
            ->exists()
    )->toBeTrue();
});
