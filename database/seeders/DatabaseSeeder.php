<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RolesSeeder::class);
        $this->call(ModulesSeeder::class);
        $this->call(ModulesRolesSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(StudentsSeeder::class);
        $this->call(CountriesSeeder::class);
        $this->call(CurrenciesSeeder::class);
        $this->call(CoursesSeeder::class);
        $this->call(PricesSeeder::class);
        $this->call(CoursesPricesSeeder::class);
        $this->call(PaymentMethodsSeeder::class);
        $this->call(MessagesSeeder::class);
    }
}
