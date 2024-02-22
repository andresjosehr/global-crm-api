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
        Schema::table('sap_instalations', function (Blueprint $table) {
            $table->string('screenshot')->nullable()->after('previus_sap_instalation');
            $table->string('restrictions')->nullable()->after('screenshot');
            $table->string('payment_receipt')->nullable()->after('sap_payment_date');
            $table->integer('price_amount')->nullable()->after('price_id');
            $table->date('payment_date')->nullable()->after('price_amount');


            // drop column
            $table->dropColumn('start_datetime');
            $table->dropColumn('end_datetime');
            $table->dropColumn('price');
            $table->dropColumn('sap_payment_date');

            // Drop foreign key
            $table->dropForeign(['staff_id']);
            $table->dropColumn('staff_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sap_instalations', function (Blueprint $table) {
            $table->dropColumn('screenshot');
            $table->dropColumn('restrictions');
            $table->dropColumn('payment_receipt');
            $table->dropColumn('price_amount');
            $table->dropColumn('payment_date');

            $table->dateTime('start_datetime')->nullable()->after('order_id');
            $table->dateTime('end_datetime')->nullable()->after('start_datetime');
            $table->foreignId('staff_id')->nullable()->after('end_datetime')->constrained('users');
            $table->dateTime('sap_payment_date')->nullable()->after('price_id');
        });
    }
};
