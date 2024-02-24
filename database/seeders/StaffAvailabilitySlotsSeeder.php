<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StaffAvailabilitySlotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('staff_availability_slots')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('staff_availability_slots')->insert([
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'monday',
                'start_time' => '14:30:00',
                'end_time' => '22:00:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'tuesday',
                'start_time' => '14:30:00',
                'end_time' => '22:00:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'wednesday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'wednesday',
                'start_time' => '14:30:00',
                'end_time' => '18:30:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'thursday',
                'start_time' => '14:30:00',
                'end_time' => '22:00:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'friday',
                'start_time' => '14:30:00',
                'end_time' => '22:00:00',
            ],

            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'saturday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => self::user('ejec.ventasarmando@gmail.com'),
                'day' => 'saturday',
                'start_time' => '14:30:00',
                'end_time' => '18:30:00',
            ],

        ]);





        DB::table('staff_availability_slots')->insert([
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '19:30:00',
                'end_time' => '22:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '19:30:00',
                'end_time' => '22:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '19:30:00',
                'end_time' => '22:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '19:30:00',
                'end_time' => '22:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '19:30:00',
                'end_time' => '22:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'saturday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'saturday',
                'start_time' => '14:30:00',
                'end_time' => '22:30:00',
            ]
        ]);




        DB::table('staff_availability_slots')->insert([
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '14:30:00',
                'end_time' => '21:00:00',
            ],



            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '14:30:00',
                'end_time' => '21:00:00',
            ],


            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '14:30:00',
                'end_time' => '21:00:00',
            ],


            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '14:30:00',
                'end_time' => '21:00:00',
            ],



            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'andreecuentas22@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '14:30:00',
                'end_time' => '21:00:00',
            ],



        ]);
    }

    public function user($email)
    {
        return DB::table('users')->where('email', $email)->first()->id;
    }
}
