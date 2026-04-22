<?php

declare(strict_types=1);

use App\Models\OpcionesPortafolio;
use App\Support\PortafolioColaboradorOpciones;
use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__.'/Colaborador/Helpers.php';

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(\Database\Seeders\Inicial::class);
});

it('sincronizar crea registros cuando el toggle está activo', function (): void {
    $empresa = crearEmpresaMinima();

    PortafolioColaboradorOpciones::sincronizar($empresa, [
        'full_name' => true,
        'email' => false,
    ]);

    expect(OpcionesPortafolio::query()->where('empresa_id', $empresa->id)->count())->toBe(1);
    $row = OpcionesPortafolio::query()->where('empresa_id', $empresa->id)->first();
    expect($row->nombre)->toBe('full_name')
        ->and($row->opcion)->toBe('Nombre Completo');
});

it('sincronizar elimina registros cuando el toggle está inactivo', function (): void {
    $empresa = crearEmpresaMinima();
    OpcionesPortafolio::query()->create([
        'empresa_id' => $empresa->id,
        'nombre' => 'full_name',
        'opcion' => 'Nombre Completo',
    ]);

    PortafolioColaboradorOpciones::sincronizar($empresa, [
        'full_name' => false,
    ]);

    expect(OpcionesPortafolio::query()->where('empresa_id', $empresa->id)->count())->toBe(0);
});

it('defaultsParaEmpresa refleja las opciones guardadas', function (): void {
    $empresa = crearEmpresaMinima();
    OpcionesPortafolio::query()->create([
        'empresa_id' => $empresa->id,
        'nombre' => 'rfc',
        'opcion' => 'RFC',
    ]);

    $defaults = PortafolioColaboradorOpciones::defaultsParaEmpresa($empresa->fresh());

    expect($defaults['rfc'])->toBeTrue()
        ->and($defaults['full_name'])->toBeFalse();
});

it('expone 17 definiciones como en legacy', function (): void {
    expect(PortafolioColaboradorOpciones::definiciones())->toHaveCount(17);
});
