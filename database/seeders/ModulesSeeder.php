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
                'type'        => 'basic',
            ],
            [
                'name'        => 'Ventas',
                'description' => 'Seguimiento de gestion de ventas',
                'icon'        => 'heroicons_outline:currency-dollar',
                'path'        => 'ventas',
                'type'        => 'collapsable',
            ]
		]);


        DB::table('modules')->insert([
            [
                'name'        => 'Base General',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/base-general',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Potenciales',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/potenciales',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Base General',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/matriculados',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Base General',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/no-interesados',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Subir documento',
                'description' => '',
                'icon'        => 'heroicons_outline:document-text',
                'path'        => 'importar',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Gestionar leads',
                'description' => '',
                'icon'        => 'heroicons_outline:phone',
                'path'        => 'gestionar',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ]
		]);


    }
}
