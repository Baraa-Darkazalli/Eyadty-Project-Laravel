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
        Schema::create('session_calculations', function (Blueprint $table) {
            $table->id();

            $table->boolean('is_paid')->default(false);
            $table->timestamps();

            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('reception_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('session_calculations');
    }
};
