<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadsTrakingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('leads_traking')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('leads_traking')->insert([
            // Sistema-v2
            [
                'user_id' => null,
                'lead_id' => 1
            ]
		]);
    }
}
