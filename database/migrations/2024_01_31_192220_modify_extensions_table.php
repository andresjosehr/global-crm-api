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
        Schema::table('extensions', function (Blueprint $table) {
            // make months nullable
            $table->integer('months')->nullable()->change();
            $table->date('payment_date')->nullable()->change();
            // Add draft column
            $table->boolean('draft')->default(1)->after('payment_method_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->integer('months')->change();
            $table->date('payment_date')->change();
            $table->dropColumn('draft');
        });
    }
};
