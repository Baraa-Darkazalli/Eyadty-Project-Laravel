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
        Schema::create('disease_patients', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->timestamps();

            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('disease_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('disease_patients');
    }
};
