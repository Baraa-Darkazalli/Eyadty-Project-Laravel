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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->float('salary_rate');
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('employee_id')->unique();
            $table->unsignedBigInteger('clinic_id');
            $table->unsignedBigInteger('session_duration_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('doctors');
    }
};
