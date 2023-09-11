<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Foreing key check
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payment_methods')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Faker
        $faker = \Faker\Factory::create();

        // Perú
        DB::table('payment_methods')->insert([
            ["name" => "Transferencia bancaria"],
            ["name"=> "Binance"],
            ["name" => "Paypal"],
            ["name" => "Oxxo"],
            ["name" => "Mercado Libre"],
        ]);
    }
}
