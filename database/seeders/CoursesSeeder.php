<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('courses')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('courses')->insert([
            [ 'name' => 'SAP PP', 'type' => 'sap'],
            [ 'name' => 'SAP MM', 'type' => 'sap'],
            [ 'name' => 'SAP PM', 'type' => 'sap'],
            [ 'name' => 'SAP HCM', 'type' => 'sap'],
            [ 'name' => 'SAP INTEGRAL', 'type' => 'sap'],

            [ 'name' => 'EXCEL', 'type' => ''],
            [ 'name' => 'POWERBI', 'type' => ''],
            [ 'name' => 'POWERBI AVANZADO', 'type' => ''],
            [ 'name' => 'MS PROJECT', 'type' => ''],
        ]);
    }
}
