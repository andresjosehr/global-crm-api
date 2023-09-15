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
        Schema::create('order_courses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('course_id')->unsigned()->nullable();
            $table->foreign('course_id')->references('id')->on('courses');

            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->string('license')->nullable();

            $table->string('course_type')->nullable();

            $table->timestamp('start');
            $table->timestamp('end');

            $table->string('sap_user')->nullable();

            $table->boolean('enabled')->default(false);

            $table->longText('observation')->nullable();

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
        Schema::dropIfExists('order_courses');
    }
};
