<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('zadarma_statistics', function (Blueprint $table) {

            DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');
            // Drop all columns
            $table->id()->first()->change();
            $table->dropColumn('currency');
            $table->dropColumn('billcost');
            $table->dropColumn('billseconds');
            $table->dropColumn('hangupcause');
            $table->dropColumn('cost');
            $table->dropColumn('description');
            $table->dropColumn('z_id');
            $table->dropColumn('from');
            $table->dropColumn('to');

            $table->id()->first()->change();
            $table->string('call_id')->first();
            $table->string('clid')->first();
            $table->string('destination')->first();
            $table->integer('seconds')->first();
            $table->string('is_recorded')->first();
            $table->string('pbx_call_id')->first();
            $table->id()->first()->change();

            // move id column to the end position
            DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');

            // move id column to the end position

        });

        DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zadarma_statistics', function (Blueprint $table) {

            DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');
            // Drop all columns
            $table->id()->first()->change();
            $table->dropColumn('call_id');
            $table->dropColumn('clid');
            $table->dropColumn('destination');
            $table->dropColumn('seconds');
            $table->dropColumn('is_recorded');
            $table->dropColumn('pbx_call_id');

            $table->id()->first()->change();
            $table->string('currency')->first();
            $table->integer('billcost')->first();
            $table->integer('billseconds')->first();
            $table->string('hangupcause')->first();
            $table->integer('cost')->first();
            $table->string('description')->first();
            $table->string('z_id')->first();
            $table->string('from')->first();
            $table->string('to')->first();
            $table->id()->first()->change();

            DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');
        });

        DB::statement('ALTER TABLE `zadarma_statistics` CHANGE COLUMN `id` `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT FIRST;');
    }
};
