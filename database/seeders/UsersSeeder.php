<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('users')->insert([
            [
                'name' => 'José Andrés',
                'email' => 'andresjosehr@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => null,
            ],
            [
                'name' => 'Marina',
                'email' => 'marina@globaltecnologiasacademy.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => null,
            ],
            [
                'name' => 'José Cardozo',
                'email' => 'jose.cardozo@globaltecnologiasacademy.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => null,
            ],
            [
                'name' => 'Tectico de instalación 1',
                'email' => 'tecnicoinstalacion@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'zadarma_id' => null
            ],
            [
                'name' => 'Tectico de instalación 2',
                'email' => 'tecnicoinstalacion2@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'zadarma_id' => null
            ],


            [
                'name' => 'ALDHAIR JOSÉ CARDOZO VILLARROEL',
                'email' => 'aldhairjcardozov@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-103'
            ],
            [
                'name' => 'Leonardo Dario Valero Hidalgo',
                'email' => 'leodario28@hotmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-993'
            ],
            [
                'name' => 'Moisés Alejandro Dumont Martínez',
                'email' => 'mdumont359@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-703'
            ],
            [
                'name' => 'Gabriela Del Valle Manrique Rodriguez',
                'email' => 'manriquegabriela1@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-701'
            ],
            [
                'name' => 'Arquimedes Emanuel Castañeda Cova',
                'email' => 'arquimedescastaneda77@gmail.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-710'
            ]

        ]);




    }
}
