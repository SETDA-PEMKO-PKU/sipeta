<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@sipeta.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create Admin Organisasi
        Admin::create([
            'name' => 'Admin Organisasi',
            'email' => 'organisasi@sipeta.com',
            'password' => Hash::make('password'),
            'role' => 'admin_organisasi',
            'is_active' => true,
        ]);

        // Create Admin BKPSDM
        Admin::create([
            'name' => 'Admin BKPSDM',
            'email' => 'bkpsdm@sipeta.com',
            'password' => Hash::make('password'),
            'role' => 'admin_bkpsdm',
            'is_active' => true,
        ]);
    }
}
