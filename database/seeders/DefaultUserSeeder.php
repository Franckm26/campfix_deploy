<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create MIS user if not exists
        if (!User::where('email', 'mercuriofranck9@gmail.com')->exists()) {
            User::create([
                'name' => 'Franck Mercurio',
                'email' => 'mercuriofranck9@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'mis',
                'sti_id' => 'MIS001',
                'department' => 'MIS',
                'email_verified_at' => now(),
            ]);
        }

        // Create admin user if not exists
        if (!User::where('email', 'admin@campfix.com')->exists()) {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@campfix.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'sti_id' => 'ADMIN001',
                'department' => 'Administration',
                'email_verified_at' => now(),
            ]);
        }

        // Create student user if not exists
        if (!User::where('email', 'student@campfix.com')->exists()) {
            User::create([
                'name' => 'Test Student',
                'email' => 'student@campfix.com',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'sti_id' => 'STU001',
                'department' => 'BSIT',
                'email_verified_at' => now(),
            ]);
        }
    }
}
