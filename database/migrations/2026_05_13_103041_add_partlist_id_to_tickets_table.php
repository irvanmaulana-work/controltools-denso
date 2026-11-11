<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // partlist_id untuk referensi master agar ticket lama bisa ikut update
            $table->foreignId('partlist_id')->nullable()->after('id');
            $table->foreign('partlist_id')->references('id')->on('partlists')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['partlist_id']);
            $table->dropColumn('partlist_id');
        });
    }
};