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
            $table->string('duration')->nullable()->change();
            $table->string('remain_license')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('finish_date')->nullable()->change();
            $table->date('return_date')->nullable()->change();
            $table->date('payment_date')->nullable()->change();
            // ADD COLUMN
            $table->date('new_return_date')->nullable()->after('return_date');
            $table->date('new_finish_date')->nullable()->after('finish_date');
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
            $table->string('duration')->nullable(false)->change();
            $table->string('remain_license')->nullable(false)->change();
            $table->date('start_date')->nullable(false)->change();
            $table->date('finish_date')->nullable(false)->change();
            $table->date('return_date')->nullable(false)->change();
            $table->date('payment_date')->nullable(false)->change();
            // DROP COLUMN
            $table->dropColumn('new_return_date');
            $table->dropColumn('new_finish_date');
        });
    }
};
