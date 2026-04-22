<?php

namespace Database\Factories;

use App\Models\CartaSua;
use App\Models\Colaborador;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartaSua>
 */
class CartaSuaFactory extends Factory
{
    protected $model = CartaSua::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bimestres = [
            'Enero-Febrero 2024',
            'Marzo-Abril 2024',
            'Mayo-Junio 2024',
            'Julio-Agosto 2024',
            'Septiembre-Octubre 2024',
            'Noviembre-Diciembre 2024',
            'Enero-Febrero 2025',
            'Marzo-Abril 2025',
        ];

        $retiro = fake()->randomFloat(2, 500, 3000);
        $cesantiaVejez = fake()->randomFloat(2, 1000, 5000);
        $infonavit = fake()->randomFloat(2, 500, 2500);

        return [
            'empresa_id' => Empresa::factory(),
            'colaborador_id' => Colaborador::factory(),
            'bimestre' => fake()->randomElement($bimestres),
            'razon_social' => fake()->company().' S.A. de C.V.',
            'retiro' => $retiro,
            'cesantia_vejez' => $cesantiaVejez,
            'infonavit' => $infonavit,
            'total' => round($retiro + $cesantiaVejez + $infonavit, 2),
            'datos_origen' => null,
            'pdf_path' => null,
            'primera_visualizacion' => null,
            'ultima_visualizacion' => null,
            'firmado' => false,
            'fecha_firma' => null,
            'imagen_firma' => null,
            'nom151' => null,
            'hash_nom151' => null,
            'codigo_validacion' => null,
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn (array $attributes): array => [
            'primera_visualizacion' => null,
            'ultima_visualizacion' => null,
            'firmado' => false,
            'fecha_firma' => null,
        ]);
    }

    public function vista(): static
    {
        return $this->state(fn (array $attributes): array => [
            'primera_visualizacion' => now()->subDays(rand(1, 30)),
            'ultima_visualizacion' => now()->subDays(rand(0, 5)),
            'firmado' => false,
        ]);
    }

    public function firmada(): static
    {
        return $this->state(fn (array $attributes): array => [
            'primera_visualizacion' => now()->subDays(rand(10, 60)),
            'ultima_visualizacion' => now()->subDays(rand(1, 10)),
            'firmado' => true,
            'fecha_firma' => now()->subDays(rand(1, 10)),
        ]);
    }

    public function conPdf(): static
    {
        return $this->state(fn (array $attributes): array => [
            'pdf_path' => 'companies/empresa-ejemplo/cartas-sua/'.fake()->uuid().'/carta.pdf',
        ]);
    }

    public function conDatosOrigen(): static
    {
        return $this->state(function (array $attributes): array {
            return [
                'datos_origen' => [
                    'numero_empleado' => (string) rand(1000, 9999),
                    'razon_social' => $attributes['razon_social'],
                    'rfc' => strtoupper(fake()->bothify('????######???')),
                    'curp' => strtoupper(fake()->bothify('????######??????##')),
                    'nombre' => fake()->name(),
                    'retiro' => $attributes['retiro'],
                    'cv' => $attributes['cesantia_vejez'],
                    'infonavit' => $attributes['infonavit'],
                    'total' => $attributes['total'],
                    'bimestre' => $attributes['bimestre'],
                ],
            ];
        });
    }

    public function paraEmpresa(Empresa $empresa): static
    {
        return $this->state(fn (array $attributes): array => [
            'empresa_id' => $empresa->id,
        ]);
    }

    public function paraColaborador(Colaborador $colaborador): static
    {
        return $this->state(fn (array $attributes): array => [
            'colaborador_id' => $colaborador->id,
            'empresa_id' => $colaborador->empresa_id,
        ]);
    }
}
