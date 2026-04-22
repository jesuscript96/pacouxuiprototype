<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    $this->seed(\Database\Seeders\SpatieRolesSeeder::class);
    $this->seed(\Database\Seeders\RolesClienteSeeder::class);
    $this->seed(\Database\Seeders\EmpresaEjemploSeeder::class);
    $this->seed(\Database\Seeders\ClienteEjemploSeeder::class);
});

it('mantiene la sesión entre dos visitas al panel cliente con usuario demo', function (): void {
    $user = User::query()->where('email', 'cliente@tecben.com')->first();
    expect($user)->not->toBeNull();

    $empresaId = (int) $user->empresa_id;
    expect($empresaId)->toBeGreaterThan(0);

    $url = "/cliente/{$empresaId}";

    $this->actingAs($user)->get($url)->assertOk();
    $firstUserId = auth()->id();
    expect($firstUserId)->not->toBeNull();

    $this->actingAs($user)->get($url)->assertOk();
    expect(auth()->id())->toBe($firstUserId);
});
