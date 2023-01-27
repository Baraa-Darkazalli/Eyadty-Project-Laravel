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
        Schema::create('previous_works', function (Blueprint $table) {
            $table->id();
            $table->string('work_name');
            $table->string('work_source');
            $table->timestamps();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('country_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('previous_works');
    }
};
