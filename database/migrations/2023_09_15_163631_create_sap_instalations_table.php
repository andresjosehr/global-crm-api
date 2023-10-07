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
        Schema::create('sap_instalations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');
            $table->dateTime('start_datetime')->nullable();
            $table->dateTime('end_datetime')->nullable();
            $table->string('operating_system')->nullable();
            $table->string('pc_type')->nullable();
            $table->string('status')->nullable();

            $table->bigInteger('order_course_id')->unsigned()->nullable();
            $table->foreign('order_course_id')->references('id')->on('order_courses');

            $table->string('instalation_type')->nullable();


            $table->bigInteger('price_id')->unsigned()->nullable();
            $table->foreign('price_id')->references('id')->on('prices');


            $table->string('price')->nullable();

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');

            $table->bigInteger('payment_method_id')->unsigned()->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');

            $table->string('sap_payment_date')->nullable();

            $table->bigInteger('staff_id')->unsigned()->nullable();
            $table->foreign('staff_id')->references('id')->on('users');

            $table->longText('observation')->nullable();

            $table->boolean('payment_enabled')->default(false);

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
        Schema::dropIfExists('sap_instalations');
    }
};
