<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class YawaFactory extends Factory
{

    /**
     * The name of the factory's corresponding table.
     *
     * @var string
     */

    protected $table = 'users';

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nickname' => $this->faker->name(),
            'email' => $this->faker->email(),
            'image_id' => $this->faker->numberBetween(1, 35),
            'password' => $this->faker->password(),
        ];
    }
}
