<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user if it doesn't exist
        if (!User::where('email', 'admin@romarocitas.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@romarocitas.com',
                'password' => Hash::make('password'), // Change this in production
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]);
        }
    }
}
