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
        Schema::table('certification_tests', function (Blueprint $table) {
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->dropColumn('price');
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
            $table->dropForeign(['payment_method_id']);
            $table->dropColumn('payment_method_id');

            $table->unsignedBigInteger('due_id')->nullable()->after('order_course_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certification_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('price_id')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable();

            $table->dropForeign(['due_id']);
            $table->dropColumn('due_id');
        });
    }
};
