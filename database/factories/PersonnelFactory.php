<?php

namespace Database\Factories;

use App\Models\Personnel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Personnel>
 */
class PersonnelFactory extends Factory
{
    protected $model = Personnel::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'P_CODE' => strtolower('dev.'.fake()->unique()->bothify('user###')),
            'P_PRENOM' => $firstName,
            'P_NOM' => $lastName,
            'P_SEXE' => fake()->randomElement(['M', 'F']),
            'P_CIVILITE' => 1,
            'P_OLD_MEMBER' => 0,
            'P_GRADE' => '-',
            'P_PROFESSION' => 'SPP',
            'P_STATUT' => 'SPV',
            'P_MDP' => Hash::make('password'),
            'P_SECTION' => 0,
            'C_ID' => 0,
            'GP_ID' => 0,
            'GP_ID2' => 0,
            'P_EMAIL' => fake()->safeEmail(),
            'P_HIDE' => 0,
            'P_NB_CONNECT' => 0,
            'GP_FLAG1' => 0,
            'GP_FLAG2' => 0,
            'P_NOSPAM' => 0,
            'TP_ID' => 0,
            'NPAI' => 0,
            'SUSPENDU' => 0,
            'MONTANT_REGUL' => 0,
            'P_MAITRE' => 0,
        ];
    }
}
