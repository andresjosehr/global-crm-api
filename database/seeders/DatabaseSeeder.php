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
        $this->call(CountriesSeeder::class);
        $this->call(CitiesSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(StudentsSeeder::class);
        $this->call(CurrenciesSeeder::class);
        $this->call(CoursesSeeder::class);
        $this->call(PricesSeeder::class);
        $this->call(CoursesPricesSeeder::class);
        $this->call(PaymentMethodsSeeder::class);
        $this->call(MessagesSeeder::class);
        $this->call(DocumentTypesSeeder::class);
        $this->call(StaffAvailabilitySlotsSeeder::class);
        $this->call(SapInstalationsSeeder::class);
        $this->call(SheetsSeeder::class);
        // $this->call(LeadsSeeder::class);
    }
}
