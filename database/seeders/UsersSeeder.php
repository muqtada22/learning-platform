<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user for testing
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'professor',
            'xp_points' => 0,
        ]);
        
        // Create a test student
        User::create([
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => Hash::make('password'),
            'role' => 'student',
            'xp_points' => 0,
        ]);
        
        // Create professors
        User::factory()->count(5)->professor()->create();
        
        // Create students
        User::factory()->count(20)->student()->create();
    }
}
