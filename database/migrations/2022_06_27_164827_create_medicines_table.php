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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();

            $table->integer('num_of_doses')->nullable();
            $table->text('dose_description')->nullable();
            $table->integer('num_of_pieces')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medical_name_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medicines');
    }
};
