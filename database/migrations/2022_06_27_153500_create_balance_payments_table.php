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
        Schema::create('balance_payments', function (Blueprint $table) {
            $table->id();
            $table->double('balance');
            $table->boolean('is_added')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payment_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('balance_payments');
    }
};
