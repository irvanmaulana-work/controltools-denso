<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('requester')->nullable();
            $table->string('line_requester')->nullable();
            $table->string('partlist_name')->nullable();

            $table->string('drawing_no_supplier')->nullable();
            $table->string('size_type')->nullable();
            $table->integer('qty')->nullable();
            $table->string('sub_assy_drawing_unit')->nullable();
            $table->string('location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'requester',
                'line_requester',
                'partlist_name',
                'drawing_no_supplier',
                'size_type',
                'qty',
                'sub_assy_drawing_unit',
                'location',
            ]);
        });
    }
};