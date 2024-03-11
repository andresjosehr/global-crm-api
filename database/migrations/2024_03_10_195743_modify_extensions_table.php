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
            // drop columns
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->dropColumn('price_amount');
            $table->dropColumn('payment_date');
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');

            // due_id
            $table->unsignedBigInteger('due_id')->nullable()->after('order_course_id');
            $table->foreign('due_id')->references('id')->on('dues');
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
            // add columns
            $table->unsignedBigInteger('price_id')->nullable();
            $table->decimal('price_amount', 10, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();

            // drop columns
            $table->dropForeign(['due_id']);
            $table->dropColumn('due_id');
        });
    }
};
