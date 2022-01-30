<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // return [
        //     'nickname' => "admin",
        //     'email' => "administrador@yawara.com",
        //     'email_verified_at' => now(),
        //     'password' => bcrypt('senhasupersecreta'), // password
        //     'admin' => 1,
        //     'remember_token' => \Illuminate\Support\Str::random(10),
        // ];
        return [
            'nickname' => $this->faker->name(),
            'email' => $this->faker->email(),
            'image_id' => $this->faker->numberBetween(1, 6),
            'password' => $this->faker->password(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
