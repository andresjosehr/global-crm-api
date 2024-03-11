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
            // drop the column
            // drop foreign key
            $table->dropForeign('sap_instalations_order_course_id_foreign');
            $table->dropColumn('order_course_id');
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
            // add the column
            $table->unsignedBigInteger('order_course_id')->nullable();
            // add foreign key
            $table->foreign('order_course_id')->references('id')->on('order_courses');
        });
    }
};
