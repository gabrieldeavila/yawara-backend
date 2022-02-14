<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Tag::factory(10)->create();
        \App\Models\Image::factory(10)->create();
        \App\Models\User::factory(100)->create();
        \App\Models\User::factory(1)->create(['nickname' => "admin",
            'email' => "administrador@yawara.com",
            'email_verified_at' => now(),
            'password' => bcrypt('senhasupersecreta'), // password
            'admin' => 1,
            'remember_token' => \Illuminate\Support\Str::random(10)]);
    }
}
