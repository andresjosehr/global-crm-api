<?php

namespace App\Http\Controllers;

use App\Console\Commands\Texts\AbandonedText;
use App\Console\Commands\Texts\UnfreezingText;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Car;
use App\Models\Lead;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        return '<pre>' . Artisan::output() . '</pre>';
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

    public function checkUnfreezingAndAbandonedMsg($student)
    {


        $student = json_decode($student, true);

        $excelController = new StudentsExcelController();
        $student = $excelController->formatCourses([$student]);
        $student = $excelController->formatProgress($student);

        Log::debug("[ANDRESJOSEHR - STUDIANTE: ]", [$student]);

        $unfreezingTexts = new UnfreezingText();
        $studentsWithText = $unfreezingTexts->handle($student);

        if (count($studentsWithText) > 0) {
            return $studentsWithText[0]['text'];
        }

        $abandonedTexts = new AbandonedText();
        $studentsWithText = $abandonedTexts->handle($student);

        if (count($studentsWithText) > 0) {
            return $studentsWithText[0]['text'];
        }


        return false;
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



        $polrasProcessExcelFlag = true; // procesa el excel o no
        $polrasShowMessageFormatFlag = false; // si muestra el mensaje o el json

        $studentJson = $request->data;
        Log::debug("%s::%s - Data desde Google Sheets", [$studentJson]);
        $aProcessDates = [];
        // echo "<hr>";
        // var_dump($student);
        // return "I'm here";

        // Convert string to array
        $student = json_decode($studentJson, true);

        // Check unfreezing message and abandoned messages
        if ($text = self::checkUnfreezingAndAbandonedMsg($request->data)) {
            return ["data" => $student, "message" => $text];
        }

        // puede que el json de estudiantes sea un array "[]" o un array asociativo "{}"
        $data = (isset($student[0])) ? $student[0] : $student;

        if ($polrasProcessExcelFlag == true) :
            // Formatea los datos de los alumnos en los cursos
            // Atención Retorna un ARRAY de estudiantes. solo usar el primer estudiante del array
            // @todo REHABILITAR esta parte. Se hardcodea el estudiante para pruebas
            $excelController = new StudentsExcelController();
            $aData = $excelController->formatCourses([$data]);
            $aData = $excelController->formatProgress($aData);
            $data = $aData[0]; // solo el primer estudiante

            $excelController->fixCourses($data);
        endif;

        Log::info(json_encode($data));

        // return $data;

        // @todo Descomentar estas lineas para obtener el texto de desbloqueo
        // //  Obtiene el texto de desbloqueo
        // $unfreezingTexts = new UnfreezingText();
        // $studentsWithText = $unfreezingTexts->handle($data);

        // if (count($studentsWithText) > 0) {
        //     return $studentsWithText[0]['text'];
        // }

        $studentMessageService = new StudentMessageService($data); // gestiona la lógica de los mensajes para los estudiantes
        $processDate = Carbon::now();
        $processDate->startOfDay();
        // $processDate = $processDate->parse("2024-01-12");

        // // revisa si hoy es domingo o feriado o fin de semana largo
        // $studentMessageService->testDate($processDate);
        // return;

        // revisa si mañana es domingo o feriado o fin de semana largo
        $aProcessDates[] = $processDate->copy();
        for ($i = 1; $i <= 3; $i++) :
            $processDate = $processDate->addDay();
            if ($studentMessageService::isBusinessDay($processDate) == true) :
                break; // si mañana es día hábil, no se procesa más
            else :
                $aProcessDates[] = $processDate->copy();
            endif;
        endfor;

        // métodos en orden de prioridad
        $methods = [
            'getMessageForSAPAndFreeCourseCertification', // mas prioritario por lo multiple de cursos que tiene
            'getMessageForSAPCourseCertification',
            'getMessageForInProgressFreeCourse',
            'getMessageForCompletedFreeCourse',
            'getMessageForCertifiedCourse',
            'getMessageForExtension',
        ];


        // Intenta obtener el mensaje llamando a los métodos en orden
        foreach ($aProcessDates as $processDate) :
            // echo "procesando fecha " . $processDate->format('Y-m-d') . "<br>";
            foreach ($methods as $method) :
                $message = $studentMessageService->$method($processDate);
                if (!empty($message)) {
                    // Mensaje obtenido!
                    break;
                }
            endforeach;
            if (!empty($message)) {
                // Mensaje obtenido!
                break;
            }
        endforeach;

        if (empty($message)) :
            $message = "No se encontró mensaje para el estudiante";
        endif;

        Log::debug("%s::%s - Mensaje retornado", [$message]);

        if ($polrasShowMessageFormatFlag == true) :
            return sprintf("<pre>%s</pre>", $message);
        else :
            // @todo evaluar eliminar data de la respuesta
            return ["data" => $data, "message" => $message];
        endif;
    }


    public function updateSellsExcel($order_id)
    {
        $sheet_id = env('APP_ENV') == 'production' ? '1U5mbiPnRfpOnD336Sio-3n2X6J_xQs0E3Pspme6eiUc' : '1if36irD9uuJDWcPpYY6qElfdeTiIlEVsUZNmrwDdxWs';
        $tab_id = env('APP_ENV') == 'production' ? '301252804' : '1438941447';

        $google_sheet = new GoogleSheetController();

        $spreadsheetId = $sheet_id;
        $range = 'FEBRERO 24!A1:AH';

        // 1. Obtener los datos existentes para encontrar la primera fila vacía
        $response = $google_sheet->service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();
        $header = array_shift($rows);
        $rows = array_map(function ($row) {
            return count($row);
        }, $rows);

        $emptyRow = array_search(1, $rows) + 2; // Suponiendo que la primera columna debe estar vacía

        $order = Order::where('id', $order_id)->with('orderCourses.course', 'dues.paymentMethod', 'currency', 'student.user', 'student.documentType')->first();

        // Sort $orderCourses by type ASC
        $order->orderCourses = $order->orderCourses->sortByDesc('type');

        $courses = array_reduce($order->orderCourses->toArray(), function ($carry, $item) {
            $carry  .= $item['course']['short_name'];
            return $carry . ' + ';
        }, '');

        // TO UPPERCASE
        $courses                        = substr($courses, 0, -3);
        $order->student['courses']      = $courses;
        $start                          = $order->orderCourses->min('start');
        $order->student['start']        = Carbon::parse($start)->format('d/m/Y');
        $order->student['license']      =  strtoupper($order->orderCourses->first()->license) . ' DE LICENCIA SAP Y AULA VIRTUAL';
        $order->student['user']         = $order->student->user->name;
        $order->student['row']          = $emptyRow;
        try {
            $order->student['documento'] = $order->student->documentType->code . ' ' . $order->student->document;
        } catch (\Exception $e) {
            $order->student['documento'] = $order->student->document;
        }



        $ref = [
            'row'          => 'A',
            'name'         => 'B',
            'documento'    => 'C',
            'phone'        => 'E',
            'email'        => 'F',
            'start'        => 'AB',
            'user'         => 'AD',
            'observations' => 'AG'
        ];

        foreach ($ref as $key => $col) {
            $dataToUpdate[] = ['column' => $col, 'value' => $order->student[$key] . ''];
        }
        $cCount = count($order->orderCourses->where('type', 'paid')->toArray());
        Log::debug('Cursos: ' . count($order->orderCourses->where('type', 'paid')->toArray()));
        if ($cCount > 1 && $cCount < 5) {
            $dataToUpdate[] = ['column' => 'AC', 'value' => $order->student['license'], 'note' => '3 Meses para cada curso SAP'];
        } else {
            $dataToUpdate[] = ['column' => 'AC', 'value' => $order->student['license']];
        }

        $certificationCombo = [
            '2-10' => 'Aplica para certificación en gestión de materiales y finanzas',
            '4-10' => 'Aplica para certificación en finanzas y capital humano',
            '1-3' => 'Aplica para certificación en operaciones industriales',
            '1-2' => 'Aplica para certificación en producción y logistica',
            '2-3' => 'Aplica para certificación en logistica y mantenimiento industrial',
            '1-10' => 'Aplica para certificación en producción y finanzas',
            '1-4' => 'Aplica para certificación en planificación y capital humano',
            '2-4' => 'Aplica para certificación gestión de materiales y capital humano',

            '2-4-10'     => 'Aplica para certificación en administracion de empresas',
            '1-2-4'      => 'Aplica para certificación en procesos industriales',
            '1-2-10'     => 'Aplica para certificación en gestión de recursos empresariales',
            '1-2-3'      => 'Aplica para certificación en minería y gestión industrial',
            '1-2-3-4-10' => 'Aplica para certificación en master sap integral',
        ];

        $cert = $order->orderCourses->where('type', 'paid')->map(function ($item) {
            return $item['course_id'];
        })->toArray();

        sort($cert, SORT_NUMERIC);

        $cert = array_reduce($cert, function ($carry, $item) {
            $carry .= '-' . $item;
            return $carry;
        }, '');

        // remove first '-'
        $cert = substr($cert, 1);
        if (array_key_exists($cert, $certificationCombo)) {
            $dataToUpdate[] = ['column' => 'D', 'value' => $order->student['courses'], 'note' => $certificationCombo[$cert]];
        } else {
            $dataToUpdate[] = ['column' => 'D', 'value' => $order->student['courses']];
        }

        LOG::debug($cert);



        $col = 'G';
        if ($order->dues[0]->amount > ($order->price_amount / 2)) {
            $col = 'j';
        }


        foreach ($order->dues as $due) {
            $amount = $order->currency->iso_code == 'PEN' ? $order->currency->symbol . '.' . $due->amount : $due->amount . ' ' . $order->currency->iso_code;
            $dataToUpdate[] = ['column' => $col, 'value' => $amount];
            $col++;
            $date = Carbon::parse($due->date)->format('d/m/Y');
            $dataToUpdate[] = ['column' => $col, 'value' => $date];
            $col++;
            $dataToUpdate[] = ['column' => $col, 'value' => $due->paymentMethod ? $due->paymentMethod->name : ''];
            $col++;
        }

        // 'sheet_id'          => '1if36irD9uuJDWcPpYY6qElfdeTiIlEVsUZNmrwDdxWs',
        // 'course_row_number' => $emptyRow,
        // 'tab_id'            => '1992733426',
        // Add all this properties to the array

        $dataToUpdate = array_map(function ($item) use ($emptyRow, $spreadsheetId, $tab_id) {
            $item['sheet_id'] = $spreadsheetId;
            $item['course_row_number'] = $emptyRow;
            $item['tab_id'] = $tab_id;
            return $item;
        }, $dataToUpdate);
        // return $dataToUpdate;

        // return $dataToUpdate;

        $google_sheet = new GoogleSheetController();
        $data = $google_sheet->transformData($dataToUpdate);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return "Exito";
    }
}
