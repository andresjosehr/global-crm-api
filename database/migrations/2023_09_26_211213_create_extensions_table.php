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
        Schema::create('extensions', function (Blueprint $table) {
            $table->id();
            $table->string('months');


            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->bigInteger('order_course_id')->unsigned()->nullable();
            $table->foreign('order_course_id')->references('id')->on('order_courses');


            $table->bigInteger('price_id')->unsigned()->nullable();
            $table->foreign('price_id')->references('id')->on('prices');

            $table->integer('price_amount')->nullable();

            $table->date('payment_date')->nullable();

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');

            $table->bigInteger('payment_method_id')->unsigned()->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');


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
        Schema::dropIfExists('extensions');
    }
};
