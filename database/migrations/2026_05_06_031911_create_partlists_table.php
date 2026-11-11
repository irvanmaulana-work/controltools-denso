<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('partlists', function (Blueprint $table) {
            $table->id();
            $table->string('drawing_no_supplier');
            $table->string('name');
            $table->string('size_type');
            $table->integer('qty');
            $table->string('sub_assy_drawing_unit');
            $table->string('location');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partlists');
    }
};
