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
            ],
            [
                'name' => 'Marina',
                'email' => 'marina@globaltecnologiasacademy.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
            ],
            [
                'name' => 'José Cardozo',
                'email' => 'jose.cardozo@globaltecnologiasacademy.com',
                'password' => bcrypt('secret'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
            ]
        ]);

    }
}
