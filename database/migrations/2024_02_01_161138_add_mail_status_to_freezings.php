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
            $table->string('mail_status')->default('Pendiente')->after('remain_license');
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
            $table->dropColumn('mail_status');
        });
    }
};
