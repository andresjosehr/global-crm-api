<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulesRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('modules_roles')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $modules = Module::all();

        DB::table('modules_roles')->insert([
            [
                'module_id' => self::module('Alumnos'),
                'role_id'   => self::role('Administrador'),
            ],
        ]);
    }

    static function role($name)
    {
        return Role::where('name', $name)->first()->id;
    }

    static function module($name)
    {
        return Module::where('name', $name)->first()->id;
    }
}
