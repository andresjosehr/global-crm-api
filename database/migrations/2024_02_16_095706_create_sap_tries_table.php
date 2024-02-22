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
        Schema::create('sap_tries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sap_instalation_id')->constrained();
            $table->datetime('start_datetime')->nullable();
            $table->datetime('start_datetime_target_timezone')->nullable();
            $table->string('timezone')->nullable();
            $table->datetime('end_datetime')->nullable();
            $table->string('status')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('users');
            $table->datetime('schedule_at')->nullable();

            $table->json('zoho_data')->nullable();

            $table->bigInteger('price_id')->unsigned()->nullable();
            $table->foreign('price_id')->references('id')->on('prices');


            $table->integer('price_amount')->nullable();

            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies');


            $table->bigInteger('payment_method_id')->unsigned()->nullable();
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
            $table->date('payment_date')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->boolean('payment_enabled')->default(false);
            $table->datetime('payment_verified_at')->nullable();
            $table->bigInteger('payment_verified_by')->unsigned()->nullable();

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
        Schema::dropIfExists('sap_tries');
    }
};
