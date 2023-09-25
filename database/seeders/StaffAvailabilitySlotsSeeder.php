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
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '14:00:00',
                'end_time' => '18:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '08:30:00',
                'end_time' => '12:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '13:30:00',
                'end_time' => '17:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '09:30:00',
                'end_time' => '12:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '14:30:00',
                'end_time' => '19:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '15:00:00',
                'end_time' => '19:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion@gmail.com')->first()->id,
                'day' => 'saturday',
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
            ],
        ]);



        DB::table('staff_availability_slots')->insert([
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '08:00:00',
                'end_time' => '11:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'monday',
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '10:00:00',
                'end_time' => '13:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'tuesday',
                'start_time' => '14:30:00',
                'end_time' => '19:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '09:00:00',
                'end_time' => '12:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'wednesday',
                'start_time' => '14:00:00',
                'end_time' => '18:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '08:30:00',
                'end_time' => '11:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'thursday',
                'start_time' => '13:00:00',
                'end_time' => '17:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '10:00:00',
                'end_time' => '14:00:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'friday',
                'start_time' => '15:00:00',
                'end_time' => '19:30:00',
            ],
            [
                'user_id' => DB::table('users')->where('email', 'tecnicoinstalacion2@gmail.com')->first()->id,
                'day' => 'saturday',
                'start_time' => '11:00:00',
                'end_time' => '16:00:00',
            ],
        ]);
    }
}
