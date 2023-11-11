<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Wordpress\WpUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_CellData;



class CourseStatusController extends Controller
{


    public function index()
    {
        $data = new StudentsExcelController();
        $students = $data->index('test');
        // return json_encode($students);


        $studentsFitered = array_map(function ($student) {
            if (!$student['wp_user_id']) {
                return $student;
            }


            $col = [1 => 'CERTIFICADO', 2 => 'CERTIFICADO', 3 => 'CERTIFICADO', 4 => 'CERTIFICADO', 5 => 'CERTIFICADO', 10 => 'CERTIFICADO', 6 => 'EXC CERTIF. AVA', 7 => 'PBI CERTIFICADO', 8 => 'PBI CERTIFICADO', 9 => 'MSP CERTIFICADO'];
            $courses = array_map(function ($course) use ($student, $col) {
                if (!$course['end'] && !$course['start']) {
                    $id = $course['course_id'];
                    if ($id == 6) {
                    }

                    $course['course_status'] = 'POR HABILITAR';

                    if ($student[$col[$id]] == 'NO APLICA') {
                        $course['course_status'] = 'NO CULMINÓ';
                    }
                    if ($student[$col[$id]] == 'EMITIDO') {
                        $course['course_status'] = 'COMPLETA';
                    }
                }

                if ($course['type'] == 'paid' && $student['ACCESOS'] == 'CORREO CONGELAR') {
                    $course['course_status'] = 'POR HABILITAR';
                }

                return $course;
            }, $student['courses']);

            $paidCourses = array_filter($courses, function ($course) {
                return $course['type'] == 'paid';
            });
            $freeCourses = array_filter($courses, function ($course) {
                return $course['type'] == 'free';
            });

            $freeCourses = array_values($freeCourses);
            $paidCourses = array_values($paidCourses);

            $freeCourses = array_map(function ($course) use ($student, $col) {
                if (($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO') && $course['course_status'] == 'CURSANDO') {
                    $course['course_status'] = 'CURSANDO SIN CREDLY';
                } elseif (!($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO') && $course['course_status'] == 'CURSANDO') {
                    $course['course_status'] = 'CURSANDO';
                } elseif (($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO') && $course['course_status'] == 'COMPLETA') {
                    $course['course_status'] = 'COMPLETA SIN CREDLY';
                } elseif (!($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO') && $course['course_status'] == 'CURSANDO') {
                    $course['course_status'] = 'COMPLETA';
                } elseif ($course['course_status'] == 'POR HABILITAR') {
                    if ($student[$col[$course['course_id']]] == 'EMITIDO') {
                        $course['course_status'] = 'COMPLETA';
                    } elseif ($student[$col[$course['course_id']]] == 'EMITIDO SIN CREDLY') {
                        $course['course_status'] = 'COMPLETA SIN CREDLY';
                    } elseif ($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO' && $student[$col[$course['course_id']]] == 'REPROBADO') {
                        $course['course_status'] = 'COMPLETA SIN CREDLY';
                    } elseif (!($student['EXAMEN'] != 'Aprobado' && $student['CERTIFICADO'] != 'EMITIDO') && $student[$col[$course['course_id']]] == 'REPROBADO') {
                        $course['course_status'] = 'COMPLETA';
                    }
                }


                return $course;
            }, $freeCourses);


            $student['courses'] = array_merge($paidCourses, $freeCourses);

            return $student;
        }, $students);


        $studentsFitered = array_map(function ($student) {
            if(!$student['wp_user_id']){
                $student['courses'] = array_map(function($course){
                    $course['course_status'] = 'POR HABILITAR';
                    return $course;
                }, $student['courses']);
            }

            return $student;
        },$studentsFitered);

        $studentsFitered = array_values($studentsFitered);


        // return json_encode($studentsFitered);


        $data = [];
        foreach ($studentsFitered as $student) {
            foreach ($student['courses'] as $course) {
                $column = '';
                if ($course['type'] == 'free') {
                    $columns = [6 => 'Q', 7 => 'AE', 8 => 'AE', 9 => 'AM'];
                    $column = $columns[$course['course_id']];
                } else {
                    $column = 'L';
                }

                $data[] = [
                    'sheet_id'          => $student['sheet_id'],
                    'course_row_number' => $student['course_row_number'],
                    'column'            => $column,
                    'email'             => $student['CORREO'],
                    'tab_id'            => $student['course_tab_id'],
                    'value'             => $course['course_status'],
                ];
            }
        }



        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $data = $google_sheet->updateGoogleSheet($data);

        return json_encode(["Exito" => $studentsFitered]);
    }
}
