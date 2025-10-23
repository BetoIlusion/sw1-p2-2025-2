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

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        User::factory()->create([
            'name' => 'beto',
            'email' => 'beto@gmail.com',
            'password' => bcrypt('123456789'), // Contraseña encriptada
        ]);
        User::factory()->create([
            'name' => 'edberto',
            'email' => 'edberto@gmail.com',
            'password' => bcrypt('123456789'), // Contraseña encriptada
        ]);
        User::factory()->create([
            'name' => 'pedro',
            'email' => 'pedro@gmail.com',
            'password' => bcrypt('123456789'), // Contraseña encriptada
        ]);

        $this->call(DiagramaSeeder::class);
    }
}
