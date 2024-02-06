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
            $table->string('mail_status_unfreeze')->default('Pendiente')->after('mail_status');
            $table->string('mail_id')->nullable()->after('mail_status');
            $table->string('mail_unfreeze_id')->nullable()->after('mail_status_unfreeze');
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
            $table->dropColumn('mail_status_unfreeze');
            $table->dropColumn('mail_id');
            $table->dropColumn('mail_unfreeze_id');
        });
    }
};
