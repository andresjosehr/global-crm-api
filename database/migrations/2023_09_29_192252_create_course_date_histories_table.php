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
        Schema::create('dates_history', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_course_id')->unsigned()->nullable();
            $table->foreign('order_course_id')->references('id')->on('order_courses');

            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('type')->nullable();

            $table->bigInteger('extension_id')->unsigned()->nullable();
            $table->foreign('extension_id')->references('id')->on('extensions');

            $table->bigInteger('freezing_id')->unsigned()->nullable();
            $table->foreign('freezing_id')->references('id')->on('freezings');
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
        Schema::dropIfExists('course_date_histories');
    }
};
