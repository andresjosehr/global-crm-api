<?php

namespace Database\Seeders;

use App\Models\User;
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

        $users = [
            [
                'name' => 'José Andrés',
                'email' => 'andresjosehr@gmail.com',
                'password' => bcrypt('TN$$A5&j0S8uDoN'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => '328959-103',
            ],
            [
                'name' => 'Marina',
                'email' => 'marina@globaltecnologiasacademy.com',
                'password' => bcrypt('jNBx$Id2y7*#ihQ'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => '328959-103',
            ],
            [
                'name' => 'José Cardozo',
                'email' => 'jose.cardozo@globaltecnologiasacademy.com',
                'password' => bcrypt('42kVKHJ&&%9c9a9'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => '328959-103',
            ],
            [
                'name' => 'Tectico de instalación 1',
                'email' => 'tecnicoinstalacion@gmail.com',
                'password' => bcrypt('29S!D#us#XiuKRf'),
                'role_id' => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'zadarma_id' => null
            ],
            [
                'name' => 'Tectico de instalación 2',
                'email' => 'tecnicoinstalacion2@gmail.com',
                'password' => bcrypt('nVvvEu2@7xSEs*p'),
                'role_id' => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'zadarma_id' => null
            ],


            [
                'name' => 'ALDHAIR JOSÉ CARDOZO VILLARROEL',
                'email' => 'aldhairjcardozov@gmail.com',
                'password' => bcrypt('5fChmz8eMlhj5#L'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-103'
            ],
            [
                'name' => 'Leonardo Dario Valero Hidalgo',
                'email' => 'leodario28@hotmail.com',
                'password' => bcrypt('gkQ%2#SXz^NQx3a'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-993'
            ],
            [
                'name' => 'Moisés Alejandro Dumont Martínez',
                'email' => 'mdumont359@gmail.com',
                'password' => bcrypt('&K7NZiRaOf0C^l4'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-703'
            ],
            [
                'name' => 'Gabriela Del Valle Manrique Rodriguez',
                'email' => 'manriquegabriela1@gmail.com',
                'password' => bcrypt('wy*y8%cVkT3N&NG'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-701'
            ],
            [
                'name' => 'Arquimedes Emanuel Castañeda Cova',
                'email' => 'llazayanaalex@gmail.com',
                'password' => bcrypt('f6P@ixwg^j8xntm'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => '328959-710'
            ],
            [
                'name' => 'Alba Celeste Ceballos Briceño',
                'email' => 'asesor.gta.22@gmail.com',
                'password' => bcrypt('joUAi9sEul4gD$o'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-715' // 715
            ],
            [
                'name' => 'Alejandro Barreda',
                'email' => 'asesor.gta.28@gmail.com',
                'password' => bcrypt('hx7zJ$Y^HtCvg#s'),
                'role_id' => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'zadarma_id' => '328959-712' // 712
            ],

            [
                'name' => 'Oscar',
                'email' => 'globaltecnologiascc@gmail.com',
                'password' => bcrypt('DkhyRaLn7jY!41c'),
                'role_id' => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'zadarma_id' => '328959-103' // 712
            ],

        ];

        foreach($users as $user){
            if(!User::where('email', $user['email'])->first()){
                User::create($user);
            }
        }




    }
}
