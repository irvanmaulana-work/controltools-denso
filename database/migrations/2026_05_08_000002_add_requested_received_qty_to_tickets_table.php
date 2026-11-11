<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->integer('requested_qty')->nullable()->after('qty');
            $table->integer('received_qty')->nullable()->after('requested_qty');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn([
                'requested_qty',
                'received_qty',
            ]);
        });
    }
};
