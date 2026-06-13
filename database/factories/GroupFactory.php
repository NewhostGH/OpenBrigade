<?php

namespace Database\Factories;

use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        return [
            'GP_ID' => fake()->unique()->numberBetween(900, 999),
            'GP_DESCRIPTION' => 'DEV '.fake()->unique()->word(),
            'TR_CONFIG' => 1,
            'TR_SUB_POSSIBLE' => 0,
            'TR_ALL_POSSIBLE' => 0,
            'TR_WIDGET' => 0,
            'GP_USAGE' => 'internes',
            'GP_ASTREINTE' => 0,
            'GP_ORDER' => 50,
        ];
    }
}
