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
        Schema::table('leads', function (Blueprint $table) {
            $table->bigInteger('document_type_id')->unsigned()->nullable()->after('origin');
            $table->foreign('document_type_id')->references('id')->on('document_types');
            $table->bigInteger('country_id')->unsigned()->nullable()->after('origin');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->bigInteger('city_id')->unsigned()->nullable()->after('origin');
            $table->foreign('city_id')->references('id')->on('cities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {

        });
    }
};
