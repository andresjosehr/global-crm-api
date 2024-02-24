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
            $table->datetime('payment_verified_at')->nullable()->after('payment_enabled');
            $table->bigInteger('payment_verified_by')->unsigned()->nullable()->after('payment_verified_at');
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
            $table->dropColumn('payment_verified_at');
            $table->dropColumn('payment_verified_by');
        });
    }
};
