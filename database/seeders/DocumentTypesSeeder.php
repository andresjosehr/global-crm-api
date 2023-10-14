<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('document_types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $documents = [
            'Colombia' => [
                ['name' => 'Cédula de ciudadanía', 'code' => 'CC', 'description' => 'Cédula de ciudadanía'],
                ['name' => 'Tarjeta de identidad', 'code' => 'TI', 'description' => 'Tarjeta de identidad para menores de edad'],
            ],
            'Argentina' => [
                ['name' => 'DNI', 'code' => 'DNI', 'description' => 'Documento Nacional de Identidad'],
            ],
            'Perú' => [
                ['name' => 'DNI', 'code' => 'DNI', 'description' => 'Documento Nacional de Identidad'],
            ],
            'Chile' => [
                ['name' => 'RUN', 'code' => 'RUN', 'description' => 'Rol Único Nacional'],
                ['name' => 'RUT', 'code' => 'RUT', 'description' => 'Rol Único Tributario'],
            ],
            'México' => [
                ['name' => 'CURP', 'code' => 'CURP', 'description' => 'Clave Única de Registro de Población'],
                ['name' => 'INE', 'code' => 'INE', 'description' => 'Credencial para Votar con Fotografía'],
            ],
            // Agrega más países y documentos según lo necesites...
        ];

        foreach ($documents as $countryName => $types) {
            $countryId = DB::table('countries')->where('name', $countryName)->first()->id;
            foreach ($types as $type) {
                DB::table('document_types')->insert([
                    'country_id' => $countryId,
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'description' => $type['description'],
                ]);
            }
        }

        DB::table('document_types')->insert([
            'country_id' => null,
            'name' => 'Otro (Especifique)',
            'code' => 'Otro',
            'description' => 'Cualquier otro tipo de documento',
        ]);
    }
}
