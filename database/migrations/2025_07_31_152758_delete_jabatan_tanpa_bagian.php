<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus semua jabatan yang tidak memiliki bagian (parent_id null)
        DB::table('jabatans')
            ->whereNull('parent_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak bisa dikembalikan karena data sudah dihapus
        // Jika perlu, bisa dibuat ulang melalui seeder
    }
};
