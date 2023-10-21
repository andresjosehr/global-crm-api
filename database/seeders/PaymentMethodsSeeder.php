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
                "name" => "CUENTA BCP",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "YAPE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "CUENTA BBVA / CONTINENTAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "CUENTA INTERBANK SOLES",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "CUENTA INTERBANK DÓLARES",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "MERCADO PAGO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],
            [
                "name" => "LINK DE PAGO NIUBIZ",
                "currency_id" => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
            ],




            [
                "name" => "CUENTA BBVA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "CUENTA CUENCA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "CUENTA SANTANDER MEXICO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "OXXO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "PAYPAL",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "MERCADO PAGO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "CUENTA BANORTE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],
            [
                "name" => "CUENTA BANCO AZTECA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
            ],







            // COP
            [
                "name" => "CUENTA BANCOLOMBIA",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],
            [
                "name" => "CUENTA NEQUI",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],
            [
                "name" => "MERCADO PAGO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
            ],




            // CLP
            [
                "name" => "BANCO DE CHILE",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "CUENTA RUT",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],
            [
                "name" => "MERCADO PAGO",
                "currency_id" => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
            ],




            // USD (Ecuador)
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
