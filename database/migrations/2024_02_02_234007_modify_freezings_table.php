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
        Schema::table('freezings', function (Blueprint $table) {
            // drop column
            $table->dropColumn('duration');
            $table->integer('months')->nullable()->after('id');

            $table->bigInteger('price_id')->unsigned()->nullable()->after('payment_date');
            $table->foreign('price_id')->references('id')->on('prices');
            $table->integer('price_amount')->nullable()->after('price_id');
            $table->bigInteger('currency_id')->unsigned()->nullable()->after('price_amount');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->bigInteger('payment_method_id')->unsigned()->nullable()->after('currency_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('freezings', function (Blueprint $table) {
            // drop column
            $table->string('duration')->nullable()->after('id');
            $table->dropColumn('months');
            $table->dropColumn('price_id');
            $table->dropColumn('price_amount');
            $table->dropColumn('currency_id');
            $table->dropColumn('payment_method_id');
        });
    }
};
