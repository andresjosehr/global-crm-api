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
        Schema::table('order_courses', function (Blueprint $table) {
            $table->string('certification_status_excel_1')->nullable()->after('certification_status');
            $table->string('certification_status_excel_2')->nullable()->after('certification_status_excel_1');
            $table->string('certification_status_excel_3')->nullable()->after('certification_status_excel_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_courses', function (Blueprint $table) {
            $table->dropColumn('certification_status_excel_1');
            $table->dropColumn('certification_status_excel_2');
            $table->dropColumn('certification_status_excel_3');
        });
    }
};
