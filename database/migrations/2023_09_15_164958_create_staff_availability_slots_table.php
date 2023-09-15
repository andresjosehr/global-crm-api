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
        Schema::create('staff_availability_slots', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');  // e.g. '09:00:00' for 9 AM
            $table->time('end_time');    // e.g. '12:00:00' for 12 PM
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_availability_slots');
    }
};
