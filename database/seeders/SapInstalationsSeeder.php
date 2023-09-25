<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SapInstalationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sap_instalations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');


        DB::table('sap_instalations')->insert([
            [
                'staff_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'start_datetime' => '2023-09-22 09:00:00', // 9 AM
                'end_datetime' => '2023-09-22 09:30:00', // 6 PM
            ],
            [
                'staff_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'start_datetime' => '2023-09-22 09:00:00', // 9 AM
                'end_datetime' => '2023-09-22 09:30:00', // 6 PM
            ],

            [
                'staff_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'start_datetime' => '2023-09-23 10:30:00', // 9 AM
                'end_datetime' => '2023-09-23 10:00:00', // 6 PM
            ],
        ]);
    }
}
