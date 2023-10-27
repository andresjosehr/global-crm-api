<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // DB::table('messages')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // $messages = [
        //     [
        //         'name' => 'Extension cursos obsequios',
        //         'remaining' => '1 Mes',
        //         'content' => `'
        //     ],
        // ];


        // foreach ($messages as &$message) {
        //     // Verificar si el índice 'content' existe, en caso contrario, buscar 'description'
        //     $key = isset($message['content']) ? 'content' : 'description';

        //     // Separamos el contenido en párrafos
        //     $paragraphs = explode("\n", $message[$key]);

        //     // Eliminamos los espacios iniciales de cada párrafo
        //     $trimmedParagraphs = array_map(function ($paragraph) {
        //         return ltrim($paragraph);
        //     }, $paragraphs);

        //     // Volvemos a unir los párrafos
        //     $message[$key] = implode("\n", $trimmedParagraphs);
        // }

        // unset($message);

        // DB::table('especial_messages')->insert($messages);
    }
}
