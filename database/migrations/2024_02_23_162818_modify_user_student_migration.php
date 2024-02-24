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
        Schema::table('user_student', function (Blueprint $table) {
            // add column
            $table->bigInteger('created_by')->unsigned()->nullable()->after('student_id');
            $table->foreign('created_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_student', function (Blueprint $table) {
            // drop column
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
