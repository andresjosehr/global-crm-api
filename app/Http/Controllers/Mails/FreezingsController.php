<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FreezingsController extends Controller
{
    public function index(Request $request)
    {
        // return ZohoToken::where('token', '<>', '')->first();
        $mode = 'prod';

        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index($mode);

        $students = array_map(function ($student) {
            $student['courses'] = array_map(function ($course) use ($student) {
                if ($course['type'] == 'paid') {
                    $course['access'] = $student["ACCESOS"];
                }
                if ($course['type'] == 'free') {
                    $cols = [6 => "EXC ACCESOS", 7 => "PBI ACCESOS", 8 => "PBI ACCESOS", 9 => "MSP ACCESOS"];
                    $course['access'] = $student[$cols[$course['course_id']]];
                }

                $course['_start'] = Carbon::parse($course['start'])->setTimezone('America/Lima')->startOfDay()->format('Y-m-d');
                $course['_now']   = Carbon::now()->setTimezone('America/Lima')->startOfDay()->format('Y-m-d');

                return $course;
            }, $student['courses']);
            return $student;
        }, $students);



        $students = array_filter($students, function ($student) {
            $courses = array_filter($student['courses'], function ($course) use ($student) {
                if (!$course['start'] || $course['access'] != 'CORREO CONGELAR') {
                    return null;
                }

                $now = Carbon::now()->setTimezone('America/Lima');
                $start = Carbon::parse($course['start'])->setTimezone('America/Lima')->startOfDay(); // Ajustamos al inicio del día
                $tomorrow = $now->copy()->startOfDay(); // Ajustamos al inicio del día
                // Add one day to tomorrow
                $tomorrow->addDay();

                // Check if start is tomorrow
                if (!$start->isSameDay($tomorrow)) {
                    return false;
                }

                return true;
            });
            return count($courses) > 0;
        });


        $students = array_values($students);


        // return ZohoToken::where('token', '<>', '')->first();
        self::sendMails($students);


        // self::updateExcel($students);

        return json_encode($students);
    }


    public function sendMails($students)
    {
        foreach ($students as $student) {

            $course = array_filter($student['courses'], function ($course) {
                if ($course['access'] == 'CORREO CONGELAR') {
                    return $course;
                }
            });
            $course_name = array_values($course)[0]['name'];

            $subject = "Continúa con tu Capacitación de $course_name con ¡Global Tecnologías Academy!";

            $content = view('mails.unfreezing', ['student' => $student])->render();

            $scheduleTime = Carbon::now()->setTimezone('America/Lima')->addDay()->format('m/d/Y');

            CoreMailsController::sendMail(
                $student['CORREO'],
                $subject,
                $content,
                $scheduleTime,
            );
        }

        return "Exito";
    }

    public function updateExcel($students)
    {

        $data = array_map(function($student){
            $student['value'] = 'PROGRAMADO (DESCONGELAR)';
            $student['column'] = 'K';
            $student['tab_id'] = $student['course_tab_id'];
            return $student;
        }, $students);


        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return true;
    }



}
