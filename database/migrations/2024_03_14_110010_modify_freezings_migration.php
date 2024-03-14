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
            // due_id
            $table->unsignedBigInteger('due_id')->nullable()->after('remain_license');
            $table->foreign('due_id')->references('id')->on('dues')->onDelete('cascade');

            $table->enum('courses', ['single', 'all'])->default('single')->after('due_id');

            $table->longText('observation')->nullable()->after('courses');
            $table->boolean('set')->default(false)->after('observation');
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
            $table->dropForeign(['due_id']);
            $table->dropColumn('due_id');
            $table->dropColumn('courses');
            $table->dropColumn('observation');
            $table->dropColumn('set');
        });
    }
};
