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
        Schema::create('freezings', function (Blueprint $table) {
            $table->id();
            $table->string('duration');
            $table->date('start_date');
            $table->date('finish_date');
            $table->date('return_date');

            $table->date('payment_date')->nullable();

            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->bigInteger('order_course_id')->unsigned()->nullable();
            $table->foreign('order_course_id')->references('id')->on('order_courses');

            $table->string('remain_license');

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
        Schema::dropIfExists('freezings');
    }
};
