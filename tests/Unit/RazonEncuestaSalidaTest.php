<?php

use App\Models\RazonEncuestaSalida;

test('soloRazonesDelCatalogo descarta textos que no son claves del CheckboxList', function () {
    $mezcladas = [
        'Cambio de residencia',
        'ABANDONO',
        'RENUNCIA',
        'Texto legacy',
    ];

    expect(RazonEncuestaSalida::soloRazonesDelCatalogo($mezcladas))
        ->toBe(['ABANDONO', 'RENUNCIA']);
});

test('opcionesCheckboxCatalogo tiene la misma lista que catalogoRazonesPermitidas', function () {
    $catalogo = RazonEncuestaSalida::catalogoRazonesPermitidas();
    $opciones = RazonEncuestaSalida::opcionesCheckboxCatalogo();

    expect($opciones)->toBeArray()
        ->and(array_keys($opciones))->toBe($catalogo)
        ->and(array_values($opciones))->toBe($catalogo);
});
