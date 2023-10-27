<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Processes\StudentsExcelController;
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

    public $sheets = [
        "1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8" => 1308509451,
        "1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo" => 378979069,
        "10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec" => 283027112,
        "1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA" => 404045194
    ];

    public function index()
    {
        $data = new StudentsExcelController();
        $students = $data->index();
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

                $course = $course[0];
                $sap_status = '';

                $course_sap = array_filter($student['courses'], function ($course) {
                    return $course['type'] == 'paid';
                });
                $course_sap = array_values($course_sap);

                if (count($course_sap) > 0) {
                    $sap_status = $course_sap[0]['course_status'];
                } else {
                    $sap_status = $student['AULA SAP'];
                }

                if ($sap_status == 'CURSANDO' && $student['EXAMEN'] != 'Aprobado' && $course['course_status'] == 'CURSANDO') {
                    $course['course_status'] = 'CURSANDO SIN CREDLY';
                } elseif ($student['EXAMEN'] == 'Aprobado' && $course['course_status'] == 'CURSANDO') {
                    $course['course_status'] = 'CURSANDO';
                } elseif ($sap_status == 'CURSANDO' && $student['EXAMEN'] != 'Aprobado' && $course['course_status'] == 'COMPLETA') {
                    $course['course_status'] = 'CURSANDO SIN CREDLY';
                } elseif ($student['EXAMEN'] == 'Aprobado' && $course['course_status'] == 'COMPLETA') {
                    $course['course_status'] = 'COMPLETA';
                }

                // replace course in courses
                $courses = array_map(function ($c) use ($course) {
                    if ($c['course_id'] == $course['course_id']) {
                        $c['course_status'] = $course['course_status'];
                    }
                    return $c;
                }, $student['courses']);

                $student['courses'] = $courses;
            }
            return $student;
        }, $students);


        $studentsFitered = array_filter($studentsFitered, function ($student) {
            return count($student['courses']) > 0 && $student['wp_user_id'];
        });
        $studentsFitered = array_values($studentsFitered);

        // return $studentsFitered;

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
                    'sheet_id' => $student['sheet_id'],
                    'course_row_number' => $student['course_row_number'],
                    'column' => $column,
                    'email' => $student['CORREO'],
                    'status' => $course['course_status'],
                ];
            }
        }

        // Group by sheet_id
        $data = array_reduce($data, function ($carry, $item) {
            $carry[$item['sheet_id']][] = $item;
            return $carry;
        }, []);






        $client = new Google_Client();

        // Load credentials from the storage
        $credentialsPath = storage_path('app/public/credentials.json');

        if (file_exists($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
        } else {
            throw new Exception('Missing Google Service Account credentials file.');
        }

        $client->setApplicationName("Client_Library_Examples");
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/spreadsheets'
        ]);
        $client->setAccessType('offline');

        $this->service = new Google_Service_Sheets($client);


        foreach ($data as $sheet_id => $updates) {
            $requests = [];

            // Obtener el ID de la hoja (pestaña) usando el mapeo
            $sheetTabId = $this->sheets[$sheet_id];

            foreach ($updates as $update) {
                $range = 'CURSOS!' . $update['column'] . $update['course_row_number'];
                $requests[] = new Google_Service_Sheets_Request([
                    'updateCells' => [
                        'range' => [
                            'sheetId'          => $sheetTabId,                             // Usar el ID de la hoja (pestaña) aquí
                            'startRowIndex'    => $update['course_row_number'] - 1,
                            'endRowIndex'      => $update['course_row_number'],
                            'startColumnIndex' => self::columnLetterToNumber($update['column']),
                            'endColumnIndex'   => self::columnLetterToNumber($update['column']) + 1,
                        ],
                        'rows' => [
                            ['values' => [new Google_Service_Sheets_CellData(['userEnteredValue' => ['stringValue' => $update['status']]])]]
                        ],
                        'fields' => 'userEnteredValue'
                    ]
                ]);
            }

            $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
            $response = $this->service->spreadsheets->batchUpdate($sheet_id, $batchUpdateRequest);

        }


        return json_encode($data);
    }

    public function columnLetterToNumber($columnLabel) {
        $number = 0;
        $length = strlen($columnLabel);
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord(strtoupper($columnLabel[$i])) - ord('A') + 1);
        }
        return $number - 1;  // Restar 1 porque las columnas en Google Sheets comienzan desde 0
    }
}
