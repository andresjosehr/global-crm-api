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
        Schema::create('zadarma_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('currency'); // "currency": "USD",
            $table->integer('billcost'); // "billcost": 0,
            $table->integer('billseconds'); // "billseconds": 5,
            $table->string('hangupcause'); // "hangupcause": "16",
            $table->string('disposition'); // "disposition": "answered",
            $table->integer('cost'); // "cost": 0,
            $table->string('description'); // "description": "Colombia COMCEL - mobile",
            $table->datetime('callstart'); // "callstart": "2024-02-01 08:45:32",
            $table->string('sip'); // "sip": "261598",
            $table->string('z_id'); // "id": "65bba09eaf78444f9410d435",
            $table->string('from'); // "from": 51901019845,
            $table->string('to'); // "to
            $table->string('extension'); // "to
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
        Schema::dropIfExists('zadarma_statistics');
    }
};
