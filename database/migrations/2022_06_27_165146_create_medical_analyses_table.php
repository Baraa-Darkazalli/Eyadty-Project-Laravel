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
        Schema::create('medical_analyses', function (Blueprint $table) {
            $table->id();

            $table->text('description')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medical_analysis_name_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('medical_analyses');
    }
};
