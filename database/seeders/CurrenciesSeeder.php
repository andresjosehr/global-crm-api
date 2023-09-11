<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('currencies')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('currencies')->insert([
            [
                'iso_code' => 'MXN',
                'name' => 'Peso Mexicano',
                'symbol' => '$',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'México')->first()->id,
            ],
            [
                'iso_code' => 'USD',
                'name' => 'Dolar Americano',
                'symbol' => '$',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Estados Unidos')->first()->id,
            ],
            [
                'iso_code' => 'PEN',
                'name' => 'Sol Peruano',
                'symbol' => 'S/',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Perú')->first()->id,
            ],
            [
                // Boliviano
                'iso_code' => 'BOB',
                'name' => 'Boliviano',
                'symbol' => 'Bs',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Bolivia')->first()->id,
            ],
            [
                // Chile
                'iso_code' => 'CLP',
                'name' => 'Peso Chileno',
                'symbol' => '$',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Chile')->first()->id,
            ],
            [
                // Argentina
                'iso_code' => 'ARS',
                'name' => 'Peso Argentino',
                'symbol' => '$',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Argentina')->first()->id,
            ],
            [
                // Colombia
                'iso_code' => 'COP',
                'name' => 'Peso Colombiano',
                'symbol' => '$',
                'position' => 'left',
                'country_id' => DB::table('countries')->where('name', 'Colombia')->first()->id,
            ],

        ]);
    }
}
