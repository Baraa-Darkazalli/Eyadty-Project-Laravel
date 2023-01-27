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
        Schema::create('waitings', function (Blueprint $table) {
            $table->id();
            $table->integer('priority');
            $table->boolean('finished')->default(0);
            $table->timestamps();

            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('waitings');
    }
};
