<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Main Info
            |--------------------------------------------------------------------------
            */

            $table->string('title');

            /*
            |--------------------------------------------------------------------------
            | Relations
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('approver_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('partlist_id')
                ->nullable()
                ->constrained('partlists')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Approval
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'pending',
                'approved',
                'rejected'
            ])->default('pending');

            $table->text('remark')->nullable();

            $table->text('approval_remark')->nullable();

            $table->text('rejection_remark')->nullable();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('rejected_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Snapshot Data
            |--------------------------------------------------------------------------
            | Snapshot dipakai supaya histori ticket tetap tampil
            | walaupun master partlist berubah.
            |--------------------------------------------------------------------------
            */

            $table->string('requester')->nullable();

            $table->string('line_requester')->nullable();

            $table->string('partlist_name')->nullable();

            $table->string('drawing_no_supplier')->nullable();

            $table->string('size_type')->nullable();

            $table->unsignedInteger('qty')->default(0);

            $table->unsignedInteger('requested_qty')->nullable();

            $table->unsignedInteger('received_qty')->nullable();

            $table->string('sub_assy_drawing_unit')->nullable();

            $table->string('location')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
