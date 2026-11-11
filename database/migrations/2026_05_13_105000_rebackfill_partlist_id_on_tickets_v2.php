<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Revert intention: disable v2 by no-op.
    public function up(): void
    {
        // Re-backfill partlist_id untuk ticket lama agar detail display ikut update terbaru.
        // Berbeda dari v1: v2 update juga jika partlist_id sudah terisi (kemungkinan salah).
        // Strategy:
        // - Cari Partlist id yang cocok dengan snapshot ticket:
        //   drawing_no_supplier + size_type + sub_assy_drawing_unit (null-safe)
        // - Update hanya jika kandidat partlist ditemukan dan (opsional) berbeda dari current partlist_id.

        // MariaDB tidak mendukung UPDATE ... FROM.
        // Gunakan korelasi subquery.

        DB::statement("SET SQL_SAFE_UPDATES = 0;");

        DB::statement("
            UPDATE tickets t
            SET t.partlist_id = (
                SELECT p.id
                FROM partlists p
                WHERE
                    p.drawing_no_supplier = t.drawing_no_supplier
                    AND p.size_type = t.size_type
                    AND ( (p.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit) )
                LIMIT 1
            )
            WHERE
                (
                    (t.drawing_no_supplier IS NOT NULL AND t.size_type IS NOT NULL)
                )
                AND (
                    SELECT p2.id
                    FROM partlists p2
                    WHERE
                        p2.drawing_no_supplier = t.drawing_no_supplier
                        AND p2.size_type = t.size_type
                        AND ( (p2.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit) )
                    LIMIT 1
                ) IS NOT NULL
                AND t.partlist_id <> (
                    SELECT p.id
                    FROM partlists p
                    WHERE
                        p.drawing_no_supplier = t.drawing_no_supplier
                        AND p.size_type = t.size_type
                        AND ( (p.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit) )
                    LIMIT 1
                )
        ");

        // fallback ketika sub_assy_drawing_unit/NULL-EMPTY tidak matching strict.
        DB::statement("
            UPDATE tickets t
            SET t.partlist_id = (
                SELECT p.id
                FROM partlists p
                WHERE
                    p.drawing_no_supplier = t.drawing_no_supplier
                    AND p.size_type = t.size_type
                    AND (p.sub_assy_drawing_unit IS NULL OR p.sub_assy_drawing_unit = '')
                    AND (t.sub_assy_drawing_unit IS NULL OR t.sub_assy_drawing_unit = '')
                LIMIT 1
            )
            WHERE
                (
                    SELECT p2.id
                    FROM partlists p2
                    WHERE
                        p2.drawing_no_supplier = t.drawing_no_supplier
                        AND p2.size_type = t.size_type
                        AND (p2.sub_assy_drawing_unit IS NULL OR p2.sub_assy_drawing_unit = '')
                        AND (t.sub_assy_drawing_unit IS NULL OR t.sub_assy_drawing_unit = '')
                    LIMIT 1
                ) IS NOT NULL
                AND t.partlist_id <> (
                    SELECT p.id
                    FROM partlists p
                    WHERE
                        p.drawing_no_supplier = t.drawing_no_supplier
                        AND p.size_type = t.size_type
                        AND (p.sub_assy_drawing_unit IS NULL OR p.sub_assy_drawing_unit = '')
                        AND (t.sub_assy_drawing_unit IS NULL OR t.sub_assy_drawing_unit = '')
                    LIMIT 1
                )
        ");

        DB::statement("SET SQL_SAFE_UPDATES = 1;");
    }

    public function down(): void
    {
        // Jangan rollback otomatis.
        Schema::table('tickets', function (Blueprint $table) {
            // no-op
        });
    }
};