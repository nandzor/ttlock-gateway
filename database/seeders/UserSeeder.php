<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        // Admin users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@cctv.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Admin User',
            'email' => 'admin2@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Regular users (operators)
        User::create([
            'name' => 'Jakarta Operator',
            'email' => 'operator.jakarta@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Bandung Operator',
            'email' => 'operator.bandung@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Surabaya Operator',
            'email' => 'operator.surabaya@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Viewer users
        User::create([
            'name' => 'Dashboard Viewer',
            'email' => 'viewer@cctv.com',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        $this->command->info('Created 6 users (2 admins, 4 operators/viewers)');
    }
}
