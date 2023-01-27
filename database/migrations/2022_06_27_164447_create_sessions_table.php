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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();

            $table->text('description')->nullable();
            $table->boolean('is_review')->default(false);
            $table->string('title');
            $table->string('session_date');
            $table->string('session_time');
            $table->timestamps();

            $table->unsignedBigInteger('previous_session_id')->nullable();
            $table->unsignedBigInteger('waiting_id')->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
