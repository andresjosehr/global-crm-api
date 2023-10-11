<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('course_prices')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach(DB::table('courses')->where('type', 'paid')->get() as $course) {
            foreach(DB::table('prices')->get() as $price) {

                if(strpos($price->description, 'Dos') !== false || strpos($price->description, 'Tres') !== false || strpos($price->description, 'Cuatro') !== false || strpos($price->description, 'Cinco') !== false || strpos($price->description, 'Seis') !== false) {
                    continue;
                }

                DB::table('course_prices')->insert([
                    'course_id' => $course->id,
                    'price_id' => $price->id,
                ]);
            }
        }
    }
}
