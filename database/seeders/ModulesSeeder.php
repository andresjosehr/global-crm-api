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
                'icon'        => 'heroicons_outline:face-smile',
                'path'        => 'alumnos',
                'type'        => 'basic',
            ],
            [
                'name'        => 'Usuarios',
                'description' => 'Gestor de Usuarios',
                'icon'        => 'heroicons_outline:user-group',
                'path'        => 'usuarios',
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
                'name'        => 'Equipo de asesores',
                'description' => '',
                'icon'        => 'heroicons_outline:identification',
                'path'        => 'lista/historial-actividad',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Perfil',
                'description' => '',
                'icon'        => 'heroicons_outline:user-circle',
                'path'        => 'perfil',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Mi base',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/potenciales',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Nube de documentos',
                'description' => '',
                'icon'        => 'heroicons_outline:document-text',
                'path'        => 'importar',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Gestion de prospectos',
                'description' => '',
                'icon'        => 'heroicons_outline:phone',
                'path'        => 'gestionar',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],

            [
                'name'        => 'Agenda activa',
                'description' => '',
                'icon'        => 'heroicons_outline:calendar',
                'path'        => '/agenda-activa',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],
            [
                'name'        => 'Matriculados',
                'description' => '',
                'icon'        => 'heroicons_outline:list-bullet',
                'path'        => 'lista/matriculados',
                'type'        => 'basic',
                'parent_id'   => DB::table('modules')->where('name', 'Ventas')->first()->id
            ],


            [
                'name'        => 'Instalaciones SAP',
                'description' => '',
                'icon'        => 'heroicons_outline:computer-desktop',
                'path'        => 'instalaciones-sap',
                'type'        => 'basic',
                'parent_id'   => null
            ],

            [
                'name'        => 'Asignaciones',
                'description' => '',
                'icon'        => 'heroicons_outline:bell-alert',
                'path'        => 'asignaciones',
                'type'        => 'basic',
                'parent_id'   => null
            ],


        ]);
    }
}
