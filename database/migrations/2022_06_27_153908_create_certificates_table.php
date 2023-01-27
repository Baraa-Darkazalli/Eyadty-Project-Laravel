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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->date('certificate_date')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('certificate_name_id');
            $table->unsignedBigInteger('certificate_source_id');
            $table->unsignedBigInteger('certificate_rating_id');
            $table->unsignedBigInteger('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('certificates');
    }
};
