<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('roles')->insert([
            [
                'name' => 'Administrador',
                'description' => 'Administrador del sistema',
            ],
            [
                'name' => 'Asesor de ventas',
                'description' => '',
            ],
            [
                'name' => 'Asesor academico de cobranza',
                'description' => '',
            ],
            [
                'name' => 'Asesor academico',
                'description' => '',
            ],
            [
                'name' => 'Tecnico de instalación',
                'description' => '',
            ]
        ]);
    }
}
