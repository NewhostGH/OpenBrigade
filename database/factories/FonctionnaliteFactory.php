<?php

namespace Database\Factories;

use App\Models\Fonctionnalite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fonctionnalite>
 */
class FonctionnaliteFactory extends Factory
{
    protected $model = Fonctionnalite::class;

    public function definition(): array
    {
        return [
            'F_ID' => fake()->unique()->numberBetween(9000, 9999),
            'F_LIBELLE' => 'DEV '.fake()->words(2, true),
            'F_TYPE' => 0,
            'TF_ID' => 0,
            'F_FLAG' => 0,
            'F_DESCRIPTION' => fake()->sentence(),
        ];
    }
}
