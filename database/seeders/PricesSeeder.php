<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PricesSeeder extends Seeder
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
        DB::table('prices')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Faker
        $faker = \Faker\Factory::create();

        // Perú
        DB::table('prices')->insert([
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1400,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 900,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 700,
                'months' => 6,
                'mode' => 'cuotas'
            ],

            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 500,
                'months' => 6,
                'mode' => 'contado'
            ],


            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 600,
                'months' => 6,
                'mode' => 'cuotas'
            ],

            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 450,
                'months' => 6,
                'mode' => 'contado'
            ],



            [
                'description' => 'Descuento final',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 400,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Primera campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 300,
                'months' => 3,
                'mode' => 'contado'
            ],
            [
                'description' => 'Segunda campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 260,
                'months' => 3,
                'mode' => 'contado'
            ],






            // Colombia
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1400000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 900000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 700000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 500000,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 600000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 450000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Descuento final',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 400000,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Primera campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 300000,
                'months' => 3,
                'mode' => 'contado'
            ],
            [
                'description' => 'Segunda campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 260000,
                'months' => 3,
                'mode' => 'contado'
            ],









            // Mexico
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 8600,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 6000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 3600,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2900,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 3000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2600,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Descuento final',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2100,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Primera campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2100,
                'months' => 3,
                'mode' => 'contado'
            ],
            [
                'description' => 'Segunda campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 1800,
                'months' => 3,
                'mode' => 'contado'
            ],






            // Chile
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 400000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 320000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 170000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 135000,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 150000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 120000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Descuento final',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 110000,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Primera campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 100000,
                'months' => 3,
                'mode' => 'contado'
            ],
            [
                'description' => 'Segunda campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 85000,
                'months' => 3,
                'mode' => 'contado'
            ],






            // Ecuador
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 400,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Precio normal',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 320,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 170,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Primera campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 135,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 150,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Segunda campaña',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 120,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Descuento final',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 110,
                'months' => 6,
                'mode' => 'contado'
            ],

            [
                'description' => 'Primera campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 110,
                'months' => 3,
                'mode' => 'contado'
            ],
            [
                'description' => 'Segunda campaña (PLAN PLUS)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 85,
                'months' => 3,
                'mode' => 'contado'
            ],








            // PEN

            // Dos cursos
            [
                'description' => 'Dos cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 900,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Precio normal - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 500,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Dos cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 800,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Descuento final - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 400,
                'months' => 6,
                'mode' => 'contado'
            ],

            // Tres cursos
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1100,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 700,
                'months' => 9,
                'mode' => 'contado'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 600,
                'months' => 9,
                'mode' => 'contado'
            ],


            // Cuatro cursos
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1300,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 800,
                'months' => 12,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1200,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 700,
                'months' => 12,
                'mode' => 'contado'
            ],




            // Cinco cursos
            [
                'description' => 'Cinco cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1500,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Precio normal - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 700,
                'months' => 15,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cinco cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1400,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Descuento final - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 600,
                'months' => 15,
                'mode' => 'contado'
            ],

            // Seis cursos
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1700,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1000,
                'months' => 18,
                'mode' => 'contado'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 1600,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 900,
                'months' => 18,
                'mode' => 'contado'
            ],




            // COP

            // Dos cursos
            [
                'description' => 'Dos cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 900000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Precio normal - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 500000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Dos cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 800000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Descuento final - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 400000,
                'months' => 6,
                'mode' => 'contado'
            ],

            // Tres cursos
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1100000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 700000,
                'months' => 9,
                'mode' => 'contado'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1000000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 600000,
                'months' => 9,
                'mode' => 'contado'
            ],

            // Cuatro cursos
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1300000,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 800000,
                'months' => 12,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1200000,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 700000,
                'months' => 12,
                'mode' => 'contado'
            ],

            // Cinco cursos
            [
                'description' => 'Cinco cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1500000,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Precio normal - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 700000,
                'months' => 15,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cinco cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1400000,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Descuento final - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 600000,
                'months' => 15,
                'mode' => 'contado'
            ],

            // Seis cursos
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1700000,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1000000,
                'months' => 18,
                'mode' => 'contado'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 1600000,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 900000,
                'months' => 18,
                'mode' => 'contado'
            ],












            // MXN
            // Dos cursos
            [
                'description' => 'Dos cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 5200,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Precio normal - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2900,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Dos cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 4700,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Descuento final - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 2600,
                'months' => 6,
                'mode' => 'contado'
            ],

            // Tres cursos
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 6000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 4100,
                'months' => 9,
                'mode' => 'contado'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 5500,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 3600,
                'months' => 9,
                'mode' => 'contado'
            ],

            // Cuatro cursos
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 7200,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 4700,
                'months' => 12,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 6600,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 4100,
                'months' => 12,
                'mode' => 'contado'
            ],

            // Cinco cursos
            [
                'description' => 'Cinco cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 8400,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Precio normal - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 4200,
                'months' => 15,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cinco cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 7800,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Descuento final - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 3600,
                'months' => 15,
                'mode' => 'contado'
            ],

            // Seis cursos
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 9600,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 5900,
                'months' => 18,
                'mode' => 'contado'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 9000,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 5300,
                'months' => 18,
                'mode' => 'contado'
            ],











            // CLP

            // Dos cursos
            [
                'description' => 'Dos cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 220000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Precio normal - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 135000,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Dos cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 190000,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Descuento final - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 110000,
                'months' => 6,
                'mode' => 'contado'
            ],

            // Tres cursos
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 300000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 200000,
                'months' => 9,
                'mode' => 'contado'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 270000,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 165000,
                'months' => 9,
                'mode' => 'contado'
            ],

            // Cuatro cursos
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 360000,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 230000,
                'months' => 12,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 330000,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 195000,
                'months' => 12,
                'mode' => 'contado'
            ],

            // Cinco cursos
            [
                'description' => 'Cinco cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 420000,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Precio normal - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 260000,
                'months' => 15,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cinco cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 170000,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Descuento final - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 150000,
                'months' => 15,
                'mode' => 'contado'
            ],

            // Seis cursos
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 480000,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 290000,
                'months' => 18,
                'mode' => 'contado'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 450000,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 250000,
                'months' => 18,
                'mode' => 'contado'
            ],









            // USD

            // Dos cursos
            [
                'description' => 'Dos cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 220,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Precio normal - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 125,
                'months' => 6,
                'mode' => 'contado'
            ],
            [
                'description' => 'Dos cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 190,
                'months' => 6,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Dos cursos (Descuento final - PLAN PREMIUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 110,
                'months' => 6,
                'mode' => 'contado'
            ],



            // Tres cursos
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 300,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 200,
                'months' => 9,
                'mode' => 'contado'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 270,
                'months' => 9,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Tres cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 165,
                'months' => 9,
                'mode' => 'contado'
            ],

            // Cuatro cursos
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 360,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 230,
                'months' => 12,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 330,
                'months' => 12,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cuatro cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 195,
                'months' => 12,
                'mode' => 'contado'
            ],

            // Cinco cursos
            [
                'description' => 'Cinco cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 420,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Precio normal - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 170,
                'months' => 15,
                'mode' => 'contado'
            ],
            [
                'description' => 'Cinco cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 390,
                'months' => 15,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Cinco cursos (Descuento final - PLAN PLATINUM)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 150,
                'months' => 15,
                'mode' => 'contado'
            ],

            // Seis cursos
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 480,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Precio normal)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 290,
                'months' => 18,
                'mode' => 'contado'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 450,
                'months' => 18,
                'mode' => 'cuotas'
            ],
            [
                'description' => 'Seis cursos (Descuento final)',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 250,
                'months' => 18,
                'mode' => 'contado'
            ],








            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 20,
                'months' => 1,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 40,
                'months' => 2,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 60,
                'months' => 3,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 80,
                'months' => 4,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 100,
                'months' => 5,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 95,
                'months' => 6,
                'mode' => ''
            ],

            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 110,
                'months' => 7,
                'mode' => ''
            ],

            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 120,
                'months' => 8,
                'mode' => ''
            ],


            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 130,
                'months' => 9,
                'mode' => ''
            ],

            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 140,
                'months' => 10,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 150,
                'months' => 11,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 160,
                'months' => 12,
                'mode' => ''
            ],


            // 20USD
            // 40USD
            // 60USD
            // 80USD
            // 100USD
            // 95USD
            // 110USD
            // 120USD
            // 130USD
            // 140USD
            // 150USD
            // 160USD






            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 25,
                'months' => 1,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 40,
                'months' => 2,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 51,
                'months' => 3,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 81,
                'months' => 4,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 96,
                'months' => 5,
                'mode' => ''
            ],
            [
                'description' => 'Extension Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 115,
                'months' => 6,
                'mode' => ''
            ],






            [
                'description' => 'Instalación adicional',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 20,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Instalación adicional',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 5,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Instalación adicional',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 9000,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Instalación adicional',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 100,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Instalación adicional',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 20000,
                'months' => 0,
                'mode' => ''
            ],


            [
                'description' => 'Desbloqueo SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'PEN')->first()->id,
                'amount' => 20,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Desbloqueo SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 5,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Desbloqueo SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'CLP')->first()->id,
                'amount' => 9000,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Desbloqueo SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'MXN')->first()->id,
                'amount' => 100,
                'months' => 0,
                'mode' => ''
            ],
            [
                'description' => 'Desbloqueo SAP',
                'currency_id' => DB::table('currencies')->where('iso_code', 'COP')->first()->id,
                'amount' => 20000,
                'months' => 0,
                'mode' => ''
            ],



            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 10,
                'months' => 1,
                'mode' => ''
            ],
            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 20,
                'months' => 2,
                'mode' => ''
            ],
            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 30,
                'months' => 3,
                'mode' => ''
            ],
            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 40,
                'months' => 4,
                'mode' => ''
            ],
            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 50,
                'months' => 5,
                'mode' => ''
            ],
            [
                'description' => 'Congelacion Curso gratis',
                'currency_id' => DB::table('currencies')->where('iso_code', 'USD')->first()->id,
                'amount' => 60,
                'months' => 6,
                'mode' => ''
            ],

















        ]);
    }
}
