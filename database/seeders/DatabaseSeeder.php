<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // User::factory(10)->create();

        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $this->call([
            MovieSeeder::class,
            RoleSeeder::class,
            CinemaDataSeeder::class, // New comprehensive seeder
<<<<<<< HEAD
            LoaiGheSeeder::class,
            ComboSeeder::class,
            TinTucSeeder::class,
=======
            // LoaiGheSeeder::class,
            DatVeSeeder::class,
>>>>>>> origin/khanhPH52932
        ]);

    }
}
