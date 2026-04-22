<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Usuario colaborador (JSON tipo + campos laborales típicos).
     */
    public function colaborador(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => ['colaborador'],
            'name' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'telefono_movil' => fake()->numerify('55########'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-50 years', '-18 years')->format('Y-m-d'),
            'fecha_ingreso' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
            'periodicidad_pago' => fake()->randomElement(['SEMANAL', 'QUINCENAL', 'MENSUAL', 'CATORCENAL']),
        ]);
    }

    /**
     * Usuario cliente (panel Cliente).
     */
    public function cliente(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => ['cliente'],
            'name' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
        ]);
    }

    /**
     * Usuario administrador de plataforma (panel Admin).
     */
    public function administrador(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => ['administrador'],
            'name' => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
        ]);
    }
}
