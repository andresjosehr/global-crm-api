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
            $table->datetime('link_sent_at')->nullable()->after('sap_instalation_id');
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
            $table->dropColumn('sap_instalation_id');
        });
    }
};
