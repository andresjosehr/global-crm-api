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
            // PEN
            [
                "name" => "BCP",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "YAPE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "INTERBANK",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "BBVA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "MP PERÚ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],





            [
                "name" => "BINANCE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "MP MEXICO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "OXXO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],







            // COP
            [
                "name" => "NEQUI",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],
            [
                "name" => "BANCOLOMBIA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],

            [
                "name" => "MP COLOMBIA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],




            // CLP
            [
                "name" => "MP CHILE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "BINANCE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "WESTERN UNION",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],





            // USD (Ecuador)
            [
                "name" => "BINANCE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],
            [
                "name" => "PRODUBANCO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],
            [
                "name" => "BANCO PICHINCHA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],

            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],
            [
                "name" => "WESTERN UNION",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],
            [
                "name" => "NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],
            [
                "name" => "INTERBANK",
                "currency_id" => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
            ],


            // Bolivia
            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'BOB')->first()->id,
            ],
            [
                "name" => "NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'BOB')->first()->id,
            ],

            [
                "name" => "WESTERN UNION",
                "currency_id" => DB::table('currencies')->where('iso_code', 'BOB')->first()->id,
            ],

            # Metodos de pago no cargados
        ]);
    }
}
