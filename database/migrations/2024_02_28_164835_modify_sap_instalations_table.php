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
            // add last_sap_try_id
            $table->unsignedBigInteger('last_sap_try_id')->nullable();
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
            $table->dropColumn('last_sap_try_id');
        });
    }
};
