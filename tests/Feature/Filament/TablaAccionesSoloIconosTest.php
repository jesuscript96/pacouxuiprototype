<?php

declare(strict_types=1);

use App\Filament\Resources\Industrias\IndustriaResource;
use App\Models\Industria;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);

    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $this->user = User::factory()->administrador()->create([
        'email' => fake()->unique()->safeEmail(),
    ]);
    $this->user->assignRole($superAdminRole);

    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $this->actingAs($this->user);

    Industria::query()->create(['nombre' => 'Industria test acciones icono']);
});

it('renderiza acciones de fila como botón solo icono', function (): void {
    $url = IndustriaResource::getUrl('index', panel: 'admin');

    $this->get($url)
        ->assertOk()
        ->assertSee('fi-ac-icon-btn-action', false);
});
