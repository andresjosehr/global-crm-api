<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_CellData;


class TestStatusController extends Controller
{

    public $sheets = [
        "1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8" => 1308509451,
        "1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo" => 378979069,
        "10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec" => 283027112,
        "1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA" => 404045194
    ];

    public function index()
    {
        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index('test');
        $studentsFitered = array_map(function ($student) {
            if (!$student['wp_user_id']) {
                return $student;
            }

            if ($student['AULA SAP'] == 'ABANDONÓ') {
                $courses = array_filter($student['courses'], function ($course) {
                    return $course['type'] == 'free';
                });
                $courses = array_values($courses);
                $student['courses'] = $courses;
            }

            $freeCourses = ['EXCEL' => 6, 'PBI' => 8, 'MS PROJECT' => 9];
            $status      = ['NO APLICA', 'ABANDONÓ', 'POR HABILITAR'];
            foreach ($freeCourses as $column => $id) {

                // Check if $id exists in courses
                $course = array_filter($student['courses'], function ($course) use ($id) {
                    return $course['course_id'] == $id;
                });

                $course = array_values($course);

                if (count($course) == 0) {
                    continue;
                }

                if (in_array($student[$column], $status)) {
                    // remove course with id 6
                    $courses = array_filter($student['courses'], function ($course) use ($id) {
                        return $course['course_id'] != $id;
                    });

                    $courses = array_values($courses);
                    $student['courses'] = $courses;
                    continue;
                }
            }

            // replace course in courses
            $student['courses'] = array_map(function ($c) {

                $now = Carbon::now()->setTimezone('America/Lima');
                $end = Carbon::parse($c['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);

                if ($now->greaterThan($end) && $c['certifaction_test'] == '3 Intentos pendientes') {
                    $c['certifaction_test'] = 'No Aplica';
                }

                if ($c['course_id'] == 6) {

                    if ($c['nivel_basico']['certifaction_test'] == '3 Intentos pendientes' && $now->greaterThan($end)) {
                        $c['nivel_basico']['certifaction_test'] = 'No Aplica';
                    }
                    if ($c['nivel_intermedio']['certifaction_test'] == '3 Intentos pendientes' && $now->greaterThan($end)) {
                        $c['nivel_intermedio']['certifaction_test'] = 'No Aplica';
                    }
                    if ($c['nivel_avanzado']['certifaction_test'] == '3 Intentos pendientes' && $now->greaterThan($end)) {
                        $c['nivel_avanzado']['certifaction_test'] = 'No Aplica';
                    }


                    if (!$c['end'] && !$c['start']) {
                        $c['nivel_basico']['certifaction_test'] = '';
                        $c['nivel_intermedio']['certifaction_test'] = '';
                        $c['nivel_avanzado']['certifaction_test'] = '';
                    }
                    $c['certifaction_test'] = '';
                }

                return $c;
            }, $student['courses']);

            return $student;
        }, $students);

        // return json_encode($studentsFitered);

        $studentsFitered = array_filter($studentsFitered, function ($student) {
            return count($student['courses']) > 0 && $student['wp_user_id'];
        });
        $studentsFitered = array_values($studentsFitered);


        // return json_encode($studentsFitered);




        $data = [];
        foreach ($studentsFitered as $student) {
            foreach ($student['courses'] as $course) {
                if ($course['type'] === 'paid') {
                    $data[] = [
                        'sheet_id'          => $student['sheet_id'],
                        'course_row_number' => $student['course_row_number'],
                        'column'            => "M",
                        'email'             => $student['CORREO'],
                        'tab_id'            => $student['course_tab_id'],
                        'value'             => $course['certifaction_test'],
                    ];
                }

                if ($course['type'] === 'free') {
                    if ($course['course_id'] === 6) {
                        $levels = ['nivel_basico' => 'V', 'nivel_intermedio' => 'Y', 'nivel_avanzado' => 'AB'];
                        foreach ($levels as $name => $column) {
                            $data[] = [
                                'sheet_id'          => $student['sheet_id'],
                                'course_row_number' => $student['course_row_number'],
                                'column'            => $column,
                                'email'             => $student['CORREO'],
                                'tab_id'            => $student['course_tab_id'],
                                'value'             => $course[$name]['certifaction_test'],
                            ];
                        }
                    }

                    if ($course['course_id'] != 6) {
                        $cols = [7 => 'AJ', 8 => 'AJ', 9 => 'AR'];
                        $data[] = [
                            'sheet_id'          => $student['sheet_id'],
                            'course_row_number' => $student['course_row_number'],
                            'column'            => $cols[$course['course_id']],
                            'email'             => $student['CORREO'],
                            'tab_id'            => $student['course_tab_id'],
                            'value'             => $course['certifaction_test'],
                        ];
                    }
                }
            }
        }


        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        // return "Exito";
        return json_encode(["Exito" => $studentsFitered]);
    }

    public function columnLetterToNumber($columnLabel)
    {
        $number = 0;
        $length = strlen($columnLabel);
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord(strtoupper($columnLabel[$i])) - ord('A') + 1);
        }
        return $number - 1;  // Restar 1 porque las columnas en Google Sheets comienzan desde 0
    }
}
