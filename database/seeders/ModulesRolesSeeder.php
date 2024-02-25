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

        $modules = Module::whereNotIn('name', ["Asignados", "Asignaciones"])->get();
        $roles = Role::all();

        // All modules to Administrador
        foreach ($modules as $module) {
            DB::table('modules_roles')->insert([
                'module_id' => $module->id,
                'role_id'   => self::role('Administrador'),
            ]);
        }

        foreach ($roles as $role) {
            DB::table('modules_roles')->insert([
                'module_id' => self::module('Asignaciones'),
                'role_id'   => $role->id,
            ]);
        }


        DB::table('modules_roles')->insert([
            [
                'module_id' => self::module('Ventas'),
                'role_id'   => self::role('Asesor de ventas'),
            ],
            [
                'module_id' => self::module('Perfil'),
                'role_id'   => self::role('Asesor de ventas'),
            ],
            [
                'module_id' => self::module('Mi base'),
                'role_id'   => self::role('Asesor de ventas'),
            ],

            [
                'module_id' => self::module('Gestion de prospectos'),
                'role_id'   => self::role('Asesor de ventas'),
            ],

            [
                'module_id' => self::module('Agenda activa'),
                'role_id'   => self::role('Asesor de ventas'),
            ],

            [
                'module_id' => self::module('Matriculados'),
                'role_id'   => self::role('Asesor de ventas'),
            ],

            [
                'module_id' => self::module('Instalaciones SAP'),
                'role_id'   => self::role('Tecnico de instalación'),
            ],



            [
                'module_id' => self::module('Alumnos'),
                'role_id'   => self::role('Asesor academico de cobranza'),
            ],


            [
                'module_id' => self::module('Alumnos'),
                'role_id'   => self::role('Asesor academico'),
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
