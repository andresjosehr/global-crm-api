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
        Schema::table('sales_activities', function (Blueprint $table) {
            $table->boolean('answered')->default(false)->after('end');
            $table->longText('observation')->nullable()->after('answered');
            $table->datetime('schedule_call_datetime')->nullable()->after('observation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_activities', function (Blueprint $table) {
            //
        });
    }
};
