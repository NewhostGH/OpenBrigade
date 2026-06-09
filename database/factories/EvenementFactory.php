<?php

namespace Database\Factories;

use App\Models\Evenement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evenement>
 */
class EvenementFactory extends Factory
{
    protected $model = Evenement::class;

    public function definition(): array
    {
        return [
            'E_CODE' => fake()->unique()->numberBetween(900000, 999999),
            'TE_CODE' => 'FOR',
            'S_ID' => 0,
            'E_LIBELLE' => 'DEV Event '.fake()->words(2, true),
            'E_LIEU' => fake()->city(),
            'E_NB_DPS' => 0,
            'E_OPEN_TO_EXT' => 0,
            'E_CLOSED' => 0,
            'E_CANCELED' => 0,
            'E_MAIL1' => 0,
            'E_MAIL2' => 0,
            'E_MAIL3' => 0,
            'E_ALLOW_REINFORCEMENT' => 0,
            'TAV_ID' => 1,
            'E_FLAG1' => 0,
            'E_VISIBLE_OUTSIDE' => 0,
            'E_REPAS' => 0,
            'E_TRANSPORT' => 0,
            'E_PARTIES' => 1,
            'E_EQUIPE' => 0,
            'E_VISIBLE_INSIDE' => 1,
            'E_EXTERIEUR' => 0,
            'E_COLONNE_RENFORT' => 0,
            'E_ANOMALIE' => 0,
        ];
    }
}
