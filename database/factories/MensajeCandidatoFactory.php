<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CandidatoReclutamiento;
use App\Models\MensajeCandidato;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MensajeCandidato>
 */
class MensajeCandidatoFactory extends Factory
{
    protected $model = MensajeCandidato::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'candidato_id' => CandidatoReclutamiento::factory(),
            'user_id' => User::query()->first()?->id ?? User::factory(),
            'comentario' => fake()->paragraph(),
        ];
    }

    public function deUsuario(User $user): static
    {
        return $this->state(fn (): array => ['user_id' => $user->id]);
    }

    public function paraCandidato(CandidatoReclutamiento $candidato): static
    {
        return $this->state(fn (): array => ['candidato_id' => $candidato->id]);
    }
}
