<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ilyas',
            'email' => 'ilyas@gmail.com',
            'phone' => '12345678901',
            'address' => 'lahore pakistan',
            'password' => '1234567891',
        ]);

         User::factory()->create([
            'name' => 'user',
            'email' => 'user@gmail.com',
            'phone' => '12345678901',
            'address' => 'lahore pakistan',
            'password' => '1234567891',
        ]);

         User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'phone' => '12345678901',
            'address' => 'lahore pakistan',
            'password' => '1234567891',
        ]);
    }
}
