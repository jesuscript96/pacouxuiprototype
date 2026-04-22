<?php

namespace Database\Factories;

use App\Models\BeneficiarioColaborador;
use App\Models\Colaborador;
use App\Models\CuentaNomina;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Colaborador>
 */
class ColaboradorFactory extends Factory
{
    protected $model = Colaborador::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->firstName();
        $apellidoPaterno = fake()->lastName();
        $apellidoMaterno = fake()->lastName();

        return [
            'empresa_id' => Empresa::query()->first()?->id ?? 1,
            'nombre' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'email' => fake()->unique()->safeEmail(),
            'telefono_movil' => fake()->numerify('55########'),
            'numero_colaborador' => (string) fake()->unique()->numberBetween(1000, 99999),
            'fecha_nacimiento' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'genero' => fake()->randomElement(['M', 'F', 'Otro']),
            'curp' => strtoupper(fake()->bothify('????######??????##')),
            'rfc' => strtoupper(fake()->bothify('????######???')),
            'nss' => fake()->numerify('###########'),
            'fecha_ingreso' => fake()->dateTimeBetween('-5 years', 'now'),
            'fecha_registro_imss' => fake()->optional(0.7)->dateTimeBetween('-5 years', 'now'),
            'estado_civil' => fake()->randomElement(['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión libre']),
            'nacionalidad' => 'Mexicana',
            'direccion' => fake()->optional(0.8)->address(),
            'salario_bruto' => fake()->randomFloat(2, 8000, 80000),
            'salario_neto' => null,
            'salario_diario' => null,
            'salario_diario_integrado' => null,
            'salario_variable' => fake()->optional(0.3)->randomFloat(2, 0, 10000),
            'monto_maximo' => null,
            'periodicidad_pago' => 'MENSUAL',
            'dia_periodicidad' => 1,
            'dias_vacaciones_anuales' => 12,
            'dias_vacaciones_restantes' => 12,
            'hora_entrada' => null,
            'hora_salida' => null,
            'hora_entrada_comida' => null,
            'hora_salida_comida' => null,
            'hora_entrada_extra' => null,
            'hora_salida_extra' => null,
            'comentario_adicional' => null,
            'codigo_jefe' => null,
            'verificado' => false,
            'verificacion_carga_masiva' => false,
            'tiene_identificacion' => false,
            'fecha_verificacion_movil' => null,
            'ubicacion_id' => null,
            'departamento_id' => null,
            'area_id' => null,
            'puesto_id' => null,
            'region_id' => null,
            'centro_pago_id' => null,
            'razon_social_id' => null,
            'nombre_empresa_pago' => null,
        ];
    }

    public function conCuenta(): static
    {
        return $this->afterCreating(function (Colaborador $colaborador): void {
            $user = $colaborador->user;
            if ($user === null) {
                return;
            }
            CuentaNomina::factory()->create([
                'user_id' => $user->id,
                'colaborador_id' => $colaborador->id,
            ]);
        });
    }

    public function conBeneficiarios(int $count = 1): static
    {
        return $this->afterCreating(function (Colaborador $colaborador) use ($count): void {
            $user = $colaborador->user;
            if ($user === null) {
                return;
            }
            BeneficiarioColaborador::factory($count)->create([
                'user_id' => $user->id,
                'colaborador_id' => $colaborador->id,
            ]);
        });
    }

    public function catorcenal(): static
    {
        return $this->state(fn (array $attributes) => [
            'periodicidad_pago' => 'CATORCENAL',
            'dia_periodicidad' => 14,
        ]);
    }

    public function sinEmail(): static
    {
        return $this->state(fn (array $attributes) => ['email' => null]);
    }

    public function sinMovil(): static
    {
        return $this->state(fn (array $attributes) => ['telefono_movil' => null]);
    }

    public function verificado(): static
    {
        return $this->state(fn (array $attributes) => [
            'verificado' => true,
            'fecha_verificacion_movil' => now(),
        ]);
    }
}
