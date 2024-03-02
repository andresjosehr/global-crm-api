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
        Schema::create('liveconnect_messages_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel_id');
            $table->string('phone');
            $table->longText('message');
            $table->bigInteger('student_id')->nullable();
            $table->string('trigger')->nullable()->default('Manual');
            $table->string('message_type')->nullable();
            $table->bigInteger('tiggered_by')->nullable();
            $table->json('liveconnect_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liveconnect_messages_logs');
    }
};
