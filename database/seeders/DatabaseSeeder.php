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
        // User::factory(1)->create();
        // \App\Models\Tag::factory(10)->create();
        // \App\Models\Image::factory(10)->create();
        \App\Models\User::factory(100)->create();

    }
}
