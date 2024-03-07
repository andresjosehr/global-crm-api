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
        Schema::table('sap_tries', function (Blueprint $table) {
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->dropColumn('payment_date');
            $table->dropColumn('price_amount');
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropColumn('payment_receipt');
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');
            $table->dropColumn('payment_verified_at');
            $table->dropColumn('payment_verified_by');
            $table->dropColumn('payment_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sap_tries', function (Blueprint $table) {
            $table->unsignedBigInteger('price_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->decimal('price_amount', 10, 2)->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('payment_receipt')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->dateTime('payment_verified_at')->nullable();
            $table->string('payment_verified_by')->nullable();
            $table->boolean('payment_enabled')->default(false);
        });
    }
};
