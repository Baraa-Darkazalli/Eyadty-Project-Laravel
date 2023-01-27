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
        Schema::create('extra_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_name');
            $table->double('item_price')->nullable();
            $table->double('treatment_price');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('session_calculation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('extra_treatments');
    }
};
