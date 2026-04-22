<?php

declare(strict_types=1);

use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use App\Models\SpatieRole;
use App\Models\User;
use App\Services\Tableau\TableauEmbedUrlBuilder;
use App\Services\Tableau\TableauReportAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Config::set('tableau.base_url', 'https://tableau.test');
    Config::set('tableau.connected_app.client_id', 'test-client');
    Config::set('tableau.connected_app.secret_id', 'test-secret-id');
    Config::set('tableau.connected_app.secret_key', 'test-secret-key-at-least-32-chars!!');
    Config::set('tableau.embed_admin_username', 'admin-embed@test.com');
});

it('genera sesión Tableau para usuario tipo administrador en panel admin', function (): void {
    $user = User::factory()->administrador()->create();

    $session = app(TableauReportAccessService::class)->buildSessionForAdminPanel($user, 'rotacion_personal');

    expect($session->embedSrc)->toBe('https://tableau.test/views/Rotacion/RotacindePersonal')
        ->and($session->token)->not->toBeEmpty();
});

it('genera sesión Tableau para super_admin', function (): void {
    SpatieRole::withoutGlobalScopes()->firstOrCreate(
        ['name' => 'super_admin', 'guard_name' => 'web'],
        ['name' => 'super_admin', 'guard_name' => 'web', 'company_id' => null]
    );

    $user = User::factory()->administrador()->create();
    $user->assignRole('super_admin');

    $session = app(TableauReportAccessService::class)->buildSessionForAdminPanel($user, 'rotacion_personal');

    expect($session->embedSrc)->toContain('Rotacion')
        ->and($session->token)->not->toBeEmpty();
});

it('rechaza usuario cliente para sesión admin', function (): void {
    $user = User::factory()->cliente()->create();

    expect(fn () => app(TableauReportAccessService::class)->buildSessionForAdminPanel($user, 'rotacion_personal'))
        ->toThrow(TableauReportAccessDeniedException::class);
});

it('embedSrcForReportAdmin ignora overrides por empresa', function (): void {
    Config::set('tableau.report_path_overrides.rotacion_personal', [
        99 => 'Custom/OverridePath',
    ]);

    $url = app(TableauEmbedUrlBuilder::class)->embedSrcForReportAdmin('rotacion_personal');

    expect($url)->toBe('https://tableau.test/views/Rotacion/RotacindePersonal');
});

it('genera URL admin para informe demográficos según legacy v2', function (): void {
    $url = app(TableauEmbedUrlBuilder::class)->embedSrcForReportAdmin('demograficos');

    expect($url)->toBe('https://tableau.test/views/DEMOGRFICOS/DEMOGRFICOS');
});

it('genera URL admin para Satisfacción eNPS según legacy v2', function (): void {
    $url = app(TableauEmbedUrlBuilder::class)->embedSrcForReportAdmin('satisfaccion_enps');

    expect($url)->toBe('https://tableau.test/views/SatisfaccionColaboradoresSentiment/SentimentColaboradores');
});

it('genera URL admin para Encuestas según legacy v2', function (): void {
    $url = app(TableauEmbedUrlBuilder::class)->embedSrcForReportAdmin('encuestas');

    expect($url)->toBe('https://tableau.test/views/Encuestas/SegmentacinySentimientodeEncuestas');
});
