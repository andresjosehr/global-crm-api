<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Truncate all tables
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('modules')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('modules')->insert([
            // Sistema-v2
            [
                'name'        => 'Alumnos',
                'description' => 'Gestor de Alumnos',
                'icon'        => 'heroicons_outline:user-group',
                'path'        => 'alumnos',
            ]
		]);
    }
}
