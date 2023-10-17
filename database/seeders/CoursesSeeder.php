<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
		DB::table('courses')->truncate();
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('courses')->insert([
            [
                'name'       => 'SAP PP Planificación de la Producción',
                'short_name' => 'SAP PP',
                'type'       => 'paid',
                'wp_post_id' => 9027
            ],
            [
                'name'       => 'SAP MM Logística y Materiales',
                'short_name' => 'SAP MM',
                'type'       => 'paid',
                'wp_post_id' => 9169
            ],
            [
                'name'       => 'SAP Mantenimiento de Planta',
                'short_name' => 'SAP PM',
                'type'       => 'paid',
                'wp_post_id' => 8544
            ],
            [
                'name'       => 'SAP HCM Recursos Humanos',
                'short_name' => 'SAP HCM',
                'type'       => 'paid',
                'wp_post_id' => 9075
            ],
            [
                'name'       => 'Integral SAP USER',
                'short_name' => 'SAP INTEGRAL',
                'type'       => 'paid',
                'wp_post_id' => 18154
            ],

            [
                'name'       => 'Excel Empresarial',
                'short_name' => 'EXCEL',
                'type'       => 'free',
                'wp_post_id' => 11148
            ],
            [
                'name'       => 'Fundamentos de Power BI',
                'short_name' => 'POWERBI BASICO',
                'type'       => 'free',
                'wp_post_id' => 11279
            ],
            [
                'name'       => 'Power BI para el Análisis de Datos',
                'short_name' => 'POWERBI AVANZADO',
                'type'       => 'free',
                'wp_post_id' => 15917
            ],
            [
                'name'       => 'Fundamentos de MS Project 2019',
                'short_name' => 'MS PROJECT',
                'type'       => 'free',
                'wp_post_id' => 11274
            ],
            [
                'name'       => 'SAP FI Finanzas y Contabilidad',
                'short_name' => 'SAP FI',
                'type'       => 'paid',
                'wp_post_id' => 9225
            ],

        ]);
    }
}
