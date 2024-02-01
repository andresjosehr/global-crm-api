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
            $table->boolean('draft')->default(true)->after('payment_enabled');
            $table->boolean('previus_sap_instalation')->nullable()->change();
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
            $table->dropColumn('draft');
            $table->boolean('previus_sap_instalation')->change();

        });
    }
};
