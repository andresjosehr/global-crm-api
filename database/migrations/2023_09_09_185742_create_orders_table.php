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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('student_id')->unsigned()->nullable();
            $table->foreign('student_id')->references('id')->on('students');

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');

            $table->boolean('enrollment_sheet')->nullable();

            $table->string('payment_mode');

            $table->string('comunication_type')->nullable();

            $table->string('free_courses_date')->nullable();

            $table->bigInteger('price_id')->unsigned()->nullable();
            $table->foreign('price_id')->references('id')->on('prices');

            $table->integer('price_amount');

            $table->longText('observation')->nullable();


            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users');

            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');

            $table->string('key')->nullable();
            $table->boolean('terms_confirmed_by_student')->default(false);

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
        Schema::dropIfExists('orders');
    }
};
