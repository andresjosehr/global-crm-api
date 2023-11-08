<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleSheetController;
use Illuminate\Http\Request;

class UpdateExcelMails extends Controller
{
    public function index()
    {
        $mode = 'test';

        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index($mode);

        $studentsFiltered = array_filter($students, function($student){
            if($student['ACCESOS'] == 'PROGRAMADO' || $student['ACCESOS'] == 'PROGRAMADO (DESCONGELAR)'){
                return $student;
            }
        });

        $studentsFiltered = array_values($studentsFiltered);

        $data = array_map(function($student){
            return [
                'column'            => 'K',
                'value'             => 'ENVIADOS',
                'tab_id'            => $student['course_tab_id'],
                'course_row_number' => $student['course_row_number'],
                'sheet_id'          => $student['sheet_id'],
            ];
        }, $studentsFiltered);

        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return json_encode(["Exito" => $studentsFiltered]);
    }
}
