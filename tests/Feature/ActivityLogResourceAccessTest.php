<?php

declare(strict_types=1);

use App\Models\SpatieRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
    SpatieRole::withoutGlobalScopes()->firstOrCreate(
        ['name' => 'super_admin', 'guard_name' => 'web'],
        ['name' => 'super_admin', 'guard_name' => 'web', 'company_id' => null]
    );
});

it('permite al super_admin ver el listado de registro de actividades en el panel admin', function (): void {
    $user = User::factory()->administrador()->create([
        'email' => 'super.audit.'.uniqid('', true).'@test.com',
    ]);
    $user->assignRole('super_admin');

    $this->actingAs($user);

    $this->get('/admin/activity-logs')->assertSuccessful();
});

it('deniega el listado de registro de actividades a administradores sin rol super_admin', function (): void {
    $user = User::factory()->administrador()->create([
        'email' => 'admin.audit.'.uniqid('', true).'@test.com',
    ]);

    $this->actingAs($user);

    $this->get('/admin/activity-logs')->assertForbidden();
});
