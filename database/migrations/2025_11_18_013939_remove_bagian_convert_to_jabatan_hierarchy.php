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
        // Step 1: Drop foreign key constraint first
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        // Step 2: Convert bagian hierarchy to jabatan hierarchy
        $bagianToJabatanMap = $this->convertBagianToJabatan();

        // Step 3: Update all existing jabatan parent_id to reference the converted jabatan
        $this->updateJabatanParentReferences($bagianToJabatanMap);

        // Step 4: Add foreign key to reference jabatans table (self-referencing)
        Schema::table('jabatans', function (Blueprint $table) {
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('jabatans')
                  ->onDelete('cascade');
        });

        // Step 5: Remove bagian_id from asns table
        if (Schema::hasColumn('asns', 'bagian_id')) {
            Schema::table('asns', function (Blueprint $table) {
                if (Schema::hasColumn('asns', 'bagian_id')) {
                    $foreignKeys = DB::select("
                        SELECT CONSTRAINT_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = 'asns'
                        AND COLUMN_NAME = 'bagian_id'
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ");

                    foreach ($foreignKeys as $fk) {
                        DB::statement("ALTER TABLE asns DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}");
                    }
                }
                $table->dropColumn('bagian_id');
            });
        }

        // Step 6: Drop bagians table
        Schema::dropIfExists('bagians');
    }

    /**
     * Convert bagian structure to jabatan structure
     */
    private function convertBagianToJabatan(): array
    {
        // Get all bagian ordered by hierarchy (parents first)
        $bagians = DB::table('bagians')
            ->orderByRaw('COALESCE(parent_id, 0)')
            ->orderBy('id', 'asc')
            ->get();

        // Map old bagian_id to new jabatan_id
        $bagianToJabatanMap = [];

        foreach ($bagians as $bagian) {
            // Create a "structural" jabatan for this bagian
            $newJabatanId = DB::table('jabatans')->insertGetId([
                'nama' => 'Kepala ' . $bagian->nama,
                'opd_id' => null, // Will be set to parent jabatan ID later
                'jenis_jabatan' => 'Struktural',
                'kelas' => 9, // Default kelas for structural positions
                'kebutuhan' => 1,
                'parent_id' => null, // Will be updated later
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bagianToJabatanMap[$bagian->id] = [
                'jabatan_id' => $newJabatanId,
                'parent_id' => $bagian->parent_id,
                'opd_id' => $bagian->opd_id
            ];
        }

        // Now update parent_id for converted jabatan based on bagian hierarchy
        foreach ($bagianToJabatanMap as $bagianId => $data) {
            if ($data['parent_id'] && isset($bagianToJabatanMap[$data['parent_id']])) {
                // This bagian has a parent bagian, set parent to the converted parent jabatan
                DB::table('jabatans')
                    ->where('id', $data['jabatan_id'])
                    ->update(['parent_id' => $bagianToJabatanMap[$data['parent_id']]['jabatan_id']]);
            } else {
                // This is a root bagian, set opd_id
                DB::table('jabatans')
                    ->where('id', $data['jabatan_id'])
                    ->update(['opd_id' => $data['opd_id']]);
            }
        }

        return $bagianToJabatanMap;
    }

    /**
     * Update jabatan parent references from bagian to jabatan
     */
    private function updateJabatanParentReferences(array $bagianToJabatanMap): void
    {
        // Update all jabatan that had parent_id pointing to bagian
        foreach ($bagianToJabatanMap as $bagianId => $data) {
            DB::table('jabatans')
                ->where('parent_id', $bagianId)
                ->where('id', '!=', $data['jabatan_id']) // Don't update the converted jabatan itself
                ->update(['parent_id' => $data['jabatan_id']]);
        }

        // Jabatan with opd_id (kepala) should keep parent_id = null
        DB::table('jabatans')
            ->whereNotNull('opd_id')
            ->where('parent_id', '>', 0) // Only update if parent_id is set
            ->update(['parent_id' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it involves data transformation
        // and deletion. Manual restoration from backup would be required.
        throw new Exception('This migration cannot be reversed automatically. Please restore from backup if needed.');
    }
};
