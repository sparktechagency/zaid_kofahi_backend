<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

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

        $admin = User::create([
            'slug' => Str::random(),
            'full_name' => 'Admin',
            'user_name' => 'admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'ADMIN',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $admin->id,
        ]);

        $finance = User::create([
            'slug' => Str::random(),
            'full_name' => 'Finance',
            'user_name' => 'finance',
            'email' => 'finance@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'FINANCE',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $finance->id,
        ]);

        $support = User::create([
            'slug' => Str::random(),
            'full_name' => 'Support',
            'user_name' => 'support',
            'email' => 'support@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'SUPPORT',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $support->id,
        ]);

        $playerOne = User::create([
            'slug' => Str::random(),
            'full_name' => 'Player one',
            'user_name' => 'player@one',
            'email' => 'player.one@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'PLAYER',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $playerOne->id,
        ]);

        $playerTwo = User::create([
            'slug' => Str::random(),
            'full_name' => 'Player two',
            'user_name' => 'player@two',
            'email' => 'player.two@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'PLAYER',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $playerTwo->id,
        ]);

        $playerThree = User::create([
            'slug' => Str::random(),
            'full_name' => 'Player three',
            'user_name' => 'player@three',
            'email' => 'player.three@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'PLAYER',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $playerThree->id,
        ]);

        $organizerOne = User::create([
            'slug' => Str::random(),
            'full_name' => 'Organizer one',
            'user_name' => 'organizer@one',
            'email' => 'organizer.one@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'ORGANIZER',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $organizerOne->id,
        ]);

        $organizerTwo = User::create([
            'slug' => Str::random(),
            'full_name' => 'Organizer two',
            'user_name' => 'organizer@two',
            'email' => 'organizer.two@gmail.com',
            'email_verified_at' => now(),
            'password' => bcrypt('12345678'),
            'role' => 'ORGANIZER',
            'status' => 'Active',
        ]);

        Profile::create([
            'user_id' => $organizerTwo->id,
        ]);
    }
}
