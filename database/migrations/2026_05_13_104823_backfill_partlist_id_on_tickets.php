<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Backfill partlist_id untuk ticket lama agar display detail ikut update terbaru.
        // Strategy: mapping pakai gabungan snapshot yang lebih stabil:
        // - tickets.drawing_no_supplier  + tickets.size_type + tickets.sub_assy_drawing_unit
        // (sesuai request: gabungan minimal drawing_no_supplier + size_type)
        // MariaDB tidak mendukung UPDATE ... FROM seperti PostgreSQL.
        // Gunakan korelasi subquery.
        DB::statement("
            UPDATE tickets t
            SET partlist_id = (
                SELECT p.id
                FROM partlists p
                WHERE
                    p.drawing_no_supplier = t.drawing_no_supplier
                    AND p.size_type = t.size_type
                    AND ( (p.sub_assy_drawing_unit <=> t.sub_assy_drawing_unit) )
                LIMIT 1
            )
            WHERE
                (t.partlist_id IS NULL OR t.partlist_id = '')
        ");

        // fallback: jika sub_assy_drawing_unit kosong/null, coba mapping yang juga kosong/null
        DB::statement("
            UPDATE tickets t
            SET partlist_id = (
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
                (t.partlist_id IS NULL OR t.partlist_id = '')
                AND (t.sub_assy_drawing_unit IS NULL OR t.sub_assy_drawing_unit = '')
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Jangan otomatis unset di rollback karena bisa menghapus relasi yang sudah benar.
        // Kosongkan saja.
        Schema::table('tickets', function (Blueprint $table) {
            // no-op
        });
    }
};