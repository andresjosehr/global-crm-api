<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HolidaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // TRUNCATE
        DB::table('holidays')->truncate();

        DB::table('holidays')->insert([
            ['date' => '2024-03-28'],
            ['date' => '2024-03-29'],
            ['date' => '2024-05-01'],
            ['date' => '2024-06-07'],
            ['date' => '2024-06-29'],
            ['date' => '2024-07-23'],
            ['date' => '2024-07-28'],
            ['date' => '2024-07-29'],
            ['date' => '2024-08-06'],
            ['date' => '2024-08-30'],
            ['date' => '2024-08-30'],
            ['date' => '2024-10-08'],
            ['date' => '2024-11-01'],
            ['date' => '2024-12-09'],
        ]);
    }
}
