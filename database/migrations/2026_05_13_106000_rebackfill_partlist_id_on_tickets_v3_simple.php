<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Revert intention: disable v3 by no-op.
    public function up(): void
    {
        // v3: buat mapping partlist_id lebih agresif dan sederhana.
        // Tujuan: untuk ticket lama yang belum match karena null/empty atau perbedaan detail,
        // set partlist_id berdasarkan kombinasi minimal (drawing_no_supplier + size_type) terlebih dulu.
        // Lalu (opsional) jika sub_assy_drawing_unit tersedia dan match, override dengan match yang lebih spesifik.

        DB::statement('SET SQL_SAFE_UPDATES = 0;');

        // Pass 1: mapping minimal (drawing_no_supplier + size_type)
        DB::statement("
            UPDATE tickets t
            SET t.partlist_id = (
                SELECT p.id
                FROM partlists p
                WHERE
                    p.drawing_no_supplier = t.drawing_no_supplier
                    AND p.size_type = t.size_type
                LIMIT 1
            )
            WHERE
                t.drawing_no_supplier IS NOT NULL
                AND t.size_type IS NOT NULL
                AND (
                    t.partlist_id IS NULL
                    OR t.partlist_id = ''
                    OR t.partlist_id <> (
                        SELECT p.id
                        FROM partlists p
                        WHERE p.drawing_no_supplier = t.drawing_no_supplier
                          AND p.size_type = t.size_type
                        LIMIT 1
                    )
                )
        ");

        // Pass 2: mapping lebih spesifik jika sub_assy_drawing_unit tidak kosong/null
        DB::statement("
            UPDATE tickets t
            SET t.partlist_id = (
                SELECT p.id
                FROM partlists p
                WHERE
                    p.drawing_no_supplier = t.drawing_no_supplier
                    AND p.size_type = t.size_type
                    AND (p.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit)
                LIMIT 1
            )
            WHERE
                t.drawing_no_supplier IS NOT NULL
                AND t.size_type IS NOT NULL
                AND (t.sub_assy_drawing_unit IS NOT NULL AND t.sub_assy_drawing_unit <> '')
                AND (
                    t.partlist_id IS NULL
                    OR t.partlist_id = ''
                    OR t.partlist_id <> (
                        SELECT p.id
                        FROM partlists p
                        WHERE
                            p.drawing_no_supplier = t.drawing_no_supplier
                            AND p.size_type = t.size_type
                            AND (p.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit)
                        LIMIT 1
                    )
                )
        ");

        DB::statement('SET SQL_SAFE_UPDATES = 1;');
    }

    public function down(): void
    {
        // no-op
        Schema::table('tickets', function (Blueprint $table) {
            // no-op
        });
    }
};