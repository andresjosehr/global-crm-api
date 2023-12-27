<?php

namespace App\Http\Controllers;

use App\Console\Commands\Texts\UnfreezingText;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

use App\Services\StudentMessageService;

class ProcessesController extends Controller
{
    public function updateTestsStatus()
    {
        Artisan::call('update-tests-status');
        return Artisan::output();
    }

    public function updateCoursesStatus()
    {
        Artisan::call('update-courses-status');
        return Artisan::output();
    }

    public function updateExcelMails()
    {
        Artisan::call('update-excel-mails');
        return Artisan::output();
    }

    public function updateAulaStatus()
    {
        Artisan::call('update-aula-status');
        return Artisan::output();
    }

    public function sendUnfreezingsEmails()
    {
        Artisan::call('send-unfreezing-mails');
        return Artisan::output();
    }

    public function updateUnfreezingTexts()
    {
        Artisan::call('update-unfreezing-texts');
        return Artisan::output();
    }

    public function updateTexts()
    {
        Artisan::call('update-texts');
        return Artisan::output();
    }

    public function updateAbandoned()
    {
        Artisan::call('update-abandoned');
        return Artisan::output();
    }

    public function updateCompleteFreeCoursesText()
    {
        Artisan::call('update-complete-free-courses-text');
        return Artisan::output();
    }
    public function updatecompletefreecoursesonemonth()
    {
        Artisan::call('update-complete-free-courses-onemonth');
        return Artisan::output();
    }


    public function importLeadsFromLiveconnect(Request $request)
    {
        $headers = $request->headers->all();
        $headers = json_encode($headers);

        $body = $request->getContent();
        DB::table('live_connect_requests')->insert([
            'headers' => $headers,
            'body' => $body,
            'created_at' => now(),
            'updated_at' => now()
        ]);


        $courses_ids = [
            26845 => 'MM',
            26846 => 'FI',
            26849 => 'PM',
            26850 => 'PP',
            26851 => 'HCM',
            26852 => 'INTEGRAL',
            26874 => 'TODOS',
            0     => ''
        ];

        $courses = array_map(function ($course) use ($courses_ids) {
            return $courses_ids[$course];
        }, $request->chat['contacto']['etiquetas']);

        $courses = implode(' ', $courses);

        $lead = [
            'name'            => $request->chat['contacto']['nombre'] . ' ' . $request->chat['contacto']['apellidos'],
            'phone'           => $request->chat['contacto']['celular'],
            'status'          => 'Nuevo',
            'channel_id'      => $request->chat['id_canal'],
            'lead_project_id' => NULL,
            'email'           => NULL,
            'document'        => NULL,
            'user_id'         => NULL,
            'courses'         => $courses,
            'chat_date'       => Carbon::parse($request->chat['fecha'])->format('Y-m-d H:i:s'),
        ];

        Lead::create($lead);



        return ApiResponseController::response('ok', 200);
    }

    /**
     * Acción de Controlador que Genera el mensaje para el estudiante.
     * Lo llama la API: "/api/processes/generate-message" (ver rutas)
     * El body request es del tipo FORM URL ENCODED donde hay una variable "data" con una cadena JSON
     * 	"data"='{"row_number": 288,"NOMBRE": "Agustín salas Muñoz",...}'
     * 
     * IMPORTANTE: solo recibe el dato de un estudiante ("{}") y no de varios ("[{},{}]")
     */
    public function generateMessage(Request $request)
    {
        $student = $request->data;
        // echo "<hr>";
        // var_dump($student);
        // return "I'm here";

        // Convert string to array
        $student = json_decode($student, true);
        // echo "<hr>";
        // var_dump($student);
        // return "I'm here";

        // Formatea los datos de los alumnos en los cursos
        // Atención Retorna un ARRAY de estudiantes. solo usar el primer estudiante del array
        $excelController = new StudentsExcelController();
        $data = $excelController->formatCourses([$student]);
        $data = $excelController->formatProgress($data);


        // echo "<hr>";
        // hardcodea el estudiante
        $data[0]["courses"][0]["end"] = "2024-01-26";
        // echo $data[0]["courses"];
        echo json_encode($data);

        // @todo Descomentar estas lineas para obtener el texto de desbloqueo
        // //  Obtiene el texto de desbloqueo
        // $unfreezingTexts = new UnfreezingText();
        // $studentsWithText = $unfreezingTexts->handle($data);

        // if (count($studentsWithText) > 0) {
        //     return $studentsWithText[0]['text'];
        // }

        // Obtiene el texto para SAP

        echo "<hr>";
        $studentMessageService = new StudentMessageService();
        $message = $studentMessageService->getMessageForSAPCourseCertification($data[0], Carbon::now());

        return '';
    }
}
