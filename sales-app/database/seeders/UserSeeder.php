<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@salesapp.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        
        // Create kasir user
        User::create([
            'name' => 'Kasir Utama',
            'email' => 'kasir@salesapp.com',
            'password' => Hash::make('kasir123'),
            'role' => 'kasir',
        ]);
        
        // Create pelanggan user
        User::create([
            'name' => 'Pelanggan Demo',
            'email' => 'pelanggan@salesapp.com',
            'password' => Hash::make('pelanggan123'),
            'role' => 'pelanggan',
        ]);
        
        // Create additional sample users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
        ]);
        
        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);
    }
}
