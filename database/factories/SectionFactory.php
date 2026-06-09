<?php

namespace Database\Factories;

use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->bothify('DEV-??##'));

        return [
            'S_ID' => fake()->unique()->numberBetween(900, 999),
            'S_PARENT' => 0,
            'S_CODE' => $code,
            'S_DESCRIPTION' => 'Development '.$code,
            'S_HIDE' => 0,
            'S_INACTIVE' => 0,
            'S_ORDER' => fake()->numberBetween(1, 99),
            'SHOW_PHONE3' => 1,
            'SHOW_EMAIL3' => 1,
            'SHOW_URL' => 1,
            'S_TIMEZONE' => 'Europe/Paris',
            'NB_DAYS_BEFORE_BLOCK' => 0,
            'SMS_LOCAL_PROVIDER' => 0,
        ];
    }
}
