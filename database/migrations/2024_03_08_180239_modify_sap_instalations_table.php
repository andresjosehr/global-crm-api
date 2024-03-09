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
            $table->string('student_remember_sap_user')->nullable()->after('instalation_type');
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
            $table->dropColumn('student_remember_sap_user');
        });
    }
};
