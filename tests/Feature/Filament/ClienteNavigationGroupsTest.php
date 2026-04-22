<?php

declare(strict_types=1);

use App\Filament\Cliente\Pages\Analiticos\DemograficosTableauPage;
use App\Filament\Cliente\Pages\Storybook\ColoresPage;
use App\Filament\Cliente\Pages\Storybook\TablasEstiloNotionPage;
use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;
use App\Filament\Cliente\Resources\Regiones\RegionResource;
use Filament\Facades\Filament;

it('registra solo Storybook y UX prototype en ese orden en el panel cliente', function (): void {
    $keys = array_keys(Filament::getPanel('cliente')->getNavigationGroups());

    expect($keys)->toBe(['Storybook', 'UX prototype']);
});

it('asigna storybook y recursos al grupo de navegación esperado', function (): void {
    expect(ColoresPage::getNavigationGroup())->toBe('Storybook')
        ->and(ColaboradorResource::getNavigationGroup())->toBe('UX prototype')
        ->and(DemograficosTableauPage::getNavigationGroup())->toBe('UX prototype');
});

it('anida recursos y analíticos bajo los ítems padre de UX prototype', function (): void {
    expect(ColaboradorResource::getNavigationParentItem())->toBe('Gestión de personal')
        ->and(DemograficosTableauPage::getNavigationParentItem())->toBe('Analíticos')
        ->and(RegionResource::getNavigationParentItem())->toBe('Catálogos Colaboradores');
});

it('registra la demo interactiva de tablas estilo notion en storybook', function (): void {
    expect(TablasEstiloNotionPage::getNavigationGroup())->toBe('Storybook');
});
