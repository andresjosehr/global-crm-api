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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->boolean('requested')->default(false);
            $table->string('ruc')->nullable();
            $table->string('business_name')->nullable();
            $table->string('email')->nullable();

            $table->longText('tax_situation_proof')->nullable();
            $table->string('tax_situation')->nullable();
            $table->string('tax_regime')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('cellphone')->nullable();
            $table->string('cfdi_use')->nullable();

            $table->string('type')->nullable();

            $table->bigInteger('order_id')->unsigned()->nullable();
            $table->foreign('order_id')->references('id')->on('orders');

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
        Schema::dropIfExists('invoices');
    }
};
