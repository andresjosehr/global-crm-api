<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
            // Foreing key check
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('students')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // Faker
            $faker = \Faker\Factory::create();

            // Insert 100 students
            for ($i = 0; $i < 100; $i++) {

                // Random country
                $country = Country::with('cities')->inRandomOrder()->first();

                DB::table('students')->insert([
                    'name' => $faker->name,
                    'email' => $faker->email,
                    'phone' => $faker->phoneNumber,
                    'classroom_user' => $faker->userName,
                    'document' => $faker->randomNumber(8),
                    'country_id' => $country->id,
                    'city_id' => $country->cities->random()->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

    }
}
