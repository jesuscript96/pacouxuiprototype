<?php

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Filament\Cliente\Resources\Colaboradores\ColaboradorResource;

it('agrupa colaboradores y bajas bajo Catálogos Colaboradores', function () {
    expect(ColaboradorResource::getNavigationGroup())->toBe('Catálogos Colaboradores')
        ->and(BajaColaboradorResource::getNavigationGroup())->toBe('Catálogos Colaboradores');
});

it('ordena colaboradores antes que bajas en la navegación del grupo', function () {
    expect(ColaboradorResource::getNavigationSort())->toBe(1)
        ->and(BajaColaboradorResource::getNavigationSort())->toBe(2);
});
