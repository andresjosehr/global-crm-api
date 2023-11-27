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
                'name'       => 'José Andrés',
                'email'      => 'andresjosehr@gmail.com',
                'password'   => bcrypt('TN$$A5&j0S8uDoN'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-103',
            ],
            [
                'name'       => 'Marina',
                'email'      => 'marina@globaltecnologiasacademy.com',
                'password'   => bcrypt('jNBx$Id2y7*#ihQ'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-103',
            ],
            [
                'name'       => 'José Cardozo',
                'email'      => 'jose.cardozo@globaltecnologiasacademy.com',
                'password'   => bcrypt('42kVKHJ&&%9c9a9'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-103',
            ],
            [
                'name'       => 'Tectico de instalación 1',
                'email'      => 'tecnicoinstalacion@gmail.com',
                'password'   => bcrypt('29S!D#us#XiuKRf'),
                'role_id'    => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'photo'      => null,
                'zadarma_id' => null
            ],
            [
                'name'       => 'Tectico de instalación 2',
                'email'      => 'tecnicoinstalacion2@gmail.com',
                'password'   => bcrypt('nVvvEu2@7xSEs*p'),
                'role_id'    => DB::table('roles')->where('name', 'Tecnico de instalación')->first()->id,
                'photo'      => null,
                'zadarma_id' => null
            ],


            [
                'name'       => 'ALDHAIR JOSÉ CARDOZO VILLARROEL',
                'email'      => 'aldhairjcardozov@gmail.com',
                'password'   => bcrypt('5fChmz8eMlhj5#L'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => 'aldhair.jpeg',
                'zadarma_id' => '328959-103'
            ],
            [
                'name'       => 'Leonardo Dario Valero Hidalgo',
                'email'      => 'leodario28@hotmail.com',
                'password'   => bcrypt('gkQ%2#SXz^NQx3a'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => 'leonardo.jpeg',
                'zadarma_id' => '328959-993'
            ],
            [
                'name'       => 'Moisés Alejandro Dumont Martínez',
                'email'      => 'mdumont359@gmail.com',
                'password'   => bcrypt('&K7NZiRaOf0C^l4'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => 'moises.jpeg',
                'zadarma_id' => '328959-703'
            ],
            [
                'name'       => 'Gabriela Del Valle Manrique Rodriguez',
                'email'      => 'manriquegabriela1@gmail.com',
                'password'   => bcrypt('wy*y8%cVkT3N&NG'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => 'gabriela.jpeg',
                'zadarma_id' => '328959-701'
            ],
            [
                'name'       => 'Alex',
                'email'      => 'llazayanaalex@gmail.com',
                'password'   => bcrypt('f6P@ixwg^j8xntm'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-710'
            ],
            [
                'name'       => 'Alba Celeste Ceballos Briceño',                                      // Se le cuelga la llamada al momento de pasar
                'email'      => 'asesor.gta.22@gmail.com',
                'password'   => bcrypt('joUAi9sEul4gD$o'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-715'                                                          // 715
            ],
            [
                'name'       => 'Alejandro Barreda',                                                  // Se le desaparece el widget cuando pasa al siguiente lead
                'email'      => 'asesor.gta.28@gmail.com',
                'password'   => bcrypt('hx7zJ$Y^HtCvg#s'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-712'                                                          // 712
            ],

            [
                'name'       => 'Arquimedes Emanuel Castañeda Cova',                                  // Se le desaparece el widget cuando pasa al siguiente lead
                'email'      => 'arquimedescastaneda77@gmail.com',
                'password'   => bcrypt('wP*fUP57kRqCoff'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => 'arquimedes.jpeg',
                'zadarma_id' => '328959-710'                                                          // 710
            ],

            [
                'name'       => 'Oscar',
                'email'      => 'globaltecnologiascc@gmail.com',
                'password'   => bcrypt('DkhyRaLn7jY!41c'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-103'                                                      // 712
            ],

            [
                'name'       => 'Antonio Amoros',
                'email'      => 'antonioalejandroamoros@gmail.com',
                'password'   => bcrypt('KcLhO9GBP$0F^$Z'),
                'role_id'    => DB::table('roles')->where('name', 'Administrador')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-103'                                                      // 712
            ],




            [
                'name'       => 'Eduardo Gomez',
                'email'      => 'asesor.gta.23@gmail.com',
                'password'   => bcrypt('sut$k4KsL@79%Rb'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-711'
            ],
            [
                'name'       => 'Rosmaris Martinez',
                'email'      => 'asesor.gta.21@gmail.com',
                'password'   => bcrypt('K$tpO8FsBWxtQBH'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-714'
            ],
            [
                'name'       => 'Mariel Lopez',
                'email'      => 'asesor.gta.29@gmail.com',
                'password'   => bcrypt('d83UMdR4Hb0R%7n'),
                'role_id'    => DB::table('roles')->where('name', 'Asesor de ventas')->first()->id,
                'photo'      => null,
                'zadarma_id' => '328959-717'
            ],

        ];

        foreach($users as $user){
            if(!User::where('email', $user['email'])->first()){
                User::create($user);
            }
        }




    }
}
