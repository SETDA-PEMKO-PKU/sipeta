<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Opd;
use Illuminate\Support\Facades\Hash;

class AdminOpdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing OPDs
        $opdPendidikan = Opd::where('nama', 'Dinas Pendidikan')->first();
        $opdKesehatan = Opd::where('nama', 'Dinas Kesehatan')->first();
        $opdPerhubungan = Opd::where('nama', 'Dinas Perhubungan')->first();

        $createdCount = 0;

        // Create Admin OPD for Dinas Pendidikan
        if ($opdPendidikan) {
            Admin::updateOrCreate(
                ['email' => 'admin.pendidikan@sipeta.com'],
                [
                    'name' => 'Admin Dinas Pendidikan',
                    'password' => Hash::make('password'),
                    'role' => Admin::ROLE_ADMIN_OPD,
                    'is_active' => true,
                    'opd_id' => $opdPendidikan->id,
                ]
            );
            $this->command->info("✓ Admin OPD created for {$opdPendidikan->nama}");
            $createdCount++;
        } else {
            $this->command->warn("⚠ Dinas Pendidikan not found, skipping admin creation");
        }

        // Create Admin OPD for Dinas Kesehatan
        if ($opdKesehatan) {
            Admin::updateOrCreate(
                ['email' => 'admin.kesehatan@sipeta.com'],
                [
                    'name' => 'Admin Dinas Kesehatan',
                    'password' => Hash::make('password'),
                    'role' => Admin::ROLE_ADMIN_OPD,
                    'is_active' => true,
                    'opd_id' => $opdKesehatan->id,
                ]
            );
            $this->command->info("✓ Admin OPD created for {$opdKesehatan->nama}");
            $createdCount++;
        } else {
            $this->command->warn("⚠ Dinas Kesehatan not found, skipping admin creation");
        }

        // Create Admin OPD for Dinas Perhubungan
        if ($opdPerhubungan) {
            Admin::updateOrCreate(
                ['email' => 'admin.perhubungan@sipeta.com'],
                [
                    'name' => 'Admin Dinas Perhubungan',
                    'password' => Hash::make('password'),
                    'role' => Admin::ROLE_ADMIN_OPD,
                    'is_active' => true,
                    'opd_id' => $opdPerhubungan->id,
                ]
            );
            $this->command->info("✓ Admin OPD created for {$opdPerhubungan->nama}");
            $createdCount++;
        } else {
            $this->command->warn("⚠ Dinas Perhubungan not found, skipping admin creation");
        }

        // Create an inactive Admin OPD for testing deactivation
        if ($opdPendidikan) {
            Admin::updateOrCreate(
                ['email' => 'admin.pendidikan.inactive@sipeta.com'],
                [
                    'name' => 'Admin Pendidikan (Inactive)',
                    'password' => Hash::make('password'),
                    'role' => Admin::ROLE_ADMIN_OPD,
                    'is_active' => false,
                    'opd_id' => $opdPendidikan->id,
                ]
            );
            $this->command->info("✓ Inactive Admin OPD created for testing");
            $createdCount++;
        }

        $this->command->info("\n==========================================");
        $this->command->info("Admin OPD Seeder Summary:");
        $this->command->info("==========================================");
        $this->command->info("Total Admin OPD accounts created: {$createdCount}");
        $this->command->info("- " . ($createdCount - 1) . " active accounts");
        $this->command->info("- 1 inactive account (for testing)");
        $this->command->info("Default password: password");
        $this->command->info("==========================================\n");
    }
}
