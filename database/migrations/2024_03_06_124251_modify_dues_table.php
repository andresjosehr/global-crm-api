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
        Schema::table('dues', function (Blueprint $table) {

            $table->dateTime('payment_verified_at')->nullable()->after('payment_receipt');
            $table->unsignedBigInteger('payment_verified_by')->nullable()->after('payment_verified_at');

            $table->string('payment_reason')->nullable()->after('payment_receipt');

            // price_id
            $table->unsignedBigInteger('price_id')->nullable()->after('payment_method_id');
            $table->foreign('price_id')->references('id')->on('prices');

            $table->unsignedBigInteger('student_id')->nullable()->after('order_id');
            $table->foreign('student_id')->references('id')->on('students');

            // Make date column nullable
            $table->date('date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dues', function (Blueprint $table) {

            $table->dropColumn('payment_verified_at');
            $table->dropColumn('payment_verified_by');

            $table->dropColumn('payment_reason');
            $table->dropForeign(['price_id']);
            $table->dropColumn('price_id');
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};
