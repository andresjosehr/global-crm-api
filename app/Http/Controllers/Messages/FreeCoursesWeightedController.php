<?php

namespace App\Http\Controllers\Messages;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Illuminate\Http\Request;

class FreeCoursesWeightedController extends Controller
{
    public function index()
    {
        $data = new StudentsExcelController();
        $students = $data->index();


        $studentsFiltered = array_map(function ($student) {
            $freeCourses = array_filter($student['courses'], function ($course) use ($student) {
                if (is_null($course['end']) || is_null($course['start']) || $course['type'] == 'paid' || !$student['wp_user_id']) {
                    return false;
                }

                $start = Carbon::parse($course['start']);
                $end = Carbon::parse($course['end']);

                // Check if current date is between start and end date
                if (!Carbon::now()->between($start, $end)) {
                    return false;
                }


                if ($course['course_id'] == 6) {
                    $niveles = ['nivel_basico', 'nivel_intermedio', 'nivel_avanzado'];
                    $include = false;
                    foreach ($niveles as $nivel) {
                        if (($course[$nivel]['certifaction_test'] == 'SIN INTENTOS GRATIS' || $course[$nivel]['certifaction_test'] == 'REPROBADO') && $course[$nivel]['lesson_progress'] == 'COMPLETADO') {
                            $include = true;
                        }
                    }

                    return $include;
                }

                if ($course['course_id'] != 6) {
                    if ($course['certifaction_test'] != 'SIN INTENTOS GRATIS' && $course['certifaction_test'] != 'REPROBADO' || $course['lesson_progress'] != 'COMPLETADO') {
                        return false;
                    }
                }

                return true;
            });

            $freeCourses = array_values($freeCourses);
            if (count($freeCourses) == 0) {
                return $student;
            }

            $sap = array_filter($student['courses'], function ($course) {
                if ($course['type'] == 'paid') {
                    return true;
                }
                if (!isset($course['certifaction_test'])) {
                    return true;
                }

                if ($course['certifaction_test'] != 'APROBADO') {
                    return true;
                }

                return false;
            });
            $sap = array_values($sap);

            // Group courses by end date
            $freeCourses = array_reduce($freeCourses, function ($carry, $course) use ($sap, $student) {
                $end = Carbon::parse($course['end'])->format('Y-m-d');
                if (!isset($carry[$end])) {
                    $carry[$end] = [];
                }
                if (!isset($carry[$end]['free_courses'])) {
                    $carry[$end]['free_courses'] = [];
                }
                $carry[$end]['free_courses'][] = $course;


                $carry[$end]['include_sap'] = count($sap) > 0;
                $carry[$end]['end_date'] = $end;
                $carry[$end]['student_name'] = $student['NOMBRE'];

                // If date is between 15 days and 30 days, 'template'
                // Now with timezone America/Perú
                $nowc = Carbon::now()->setTimezone('America/Lima');
                $endc = Carbon::parse($end)->setTimezone('America/Lima');

                // Diferencia en días
                $diffDays = $nowc->diffInDays($endc) + 1;

                // Fecha un mes después de la fecha actual
                $oneMonthLater = $nowc->copy()->addMonth();

                $carry[$end]['end_date_carbon'] = $endc->format('d-m-Y');
                $carry[$end]['now_date_carbon'] = $nowc->format('d-m-Y');
                $carry[$end]['diff'] = $diffDays;

                $carry[$end]['template'] = null;

                if ($oneMonthLater->isSameDay($endc)) {
                    $carry[$end]['template'] = '1-mes';
                } elseif ($diffDays == 15) {
                    $carry[$end]['template'] = '15-dias';
                } elseif ($diffDays == 7) {
                    $carry[$end]['template'] = '7-dias';
                } elseif ($diffDays == 5 || $diffDays == 3) {
                    $carry[$end]['template'] = '5-dias';
                } elseif ($diffDays == 1) {
                    $carry[$end]['template'] = '1-dia';
                }


                return $carry;
            }, []);
            $freeCourses = array_values($freeCourses);
            $freeCourses = array_filter($freeCourses, function ($course) {
                return $course['template'];
            });
            $freeCourses = array_values($freeCourses);

            // Attach $freeCourses to student
            $student['free_courses_weighted_msg'] = $freeCourses;
            // $student['courses'] = null;
            return $student;
        }, $students);

        $studentsFiltered = array_values($studentsFiltered);
        $studentsFiltered = array_filter($studentsFiltered, function ($student) {
            return isset($student['free_courses_weighted_msg']) && count($student['free_courses_weighted_msg']) > 0;
        });
        $studentsFiltered = array_values($studentsFiltered);


        // return [count($students), count($studentsFiltered)];
        // return $studentsFiltered;


        foreach ($studentsFiltered as $i => $student) {
            foreach ($student['free_courses_weighted_msg'] as $j => $msg) {
                // Get blade template
                $text = view('especial-messages.free-courses-extension.' . $msg['template'], $msg)->render();
                // remove \n consecutives. Only two \n
                $text = preg_replace("/\n\n+/", "\n\n", $text);

                $studentsFiltered[$i]['free_courses_weighted_msg'][$j]['text'] = $text;
            }
        }

        self::updateSheets($studentsFiltered);

        return $studentsFiltered;
    }


    public function updateSheets($studentsFiltered)
    {
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

        $service = new Google_Service_Sheets($client);

        $body = new Google_Service_Sheets_ValueRange([
            'values' => [['']]
        ]);

        $params = [
            'valueInputOption' => 'RAW'
          ];

        $sheets = [
            "1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8", // https://docs.google.com/spreadsheets/d/1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8/edit#gid=810305363
            "1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo", // https://docs.google.com/spreadsheets/d/1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo/edit#gid=810305363
            "10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec", // https://docs.google.com/spreadsheets/d/10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec/edit#gid=810305363
            "1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA", // https://docs.google.com/spreadsheets/d/1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA/edit#gid=810305363
        ];

        // Update 1000 rows in each sheet in column AP
        foreach ($sheets as $sheet) {
            $range = 'CURSOS!AP2:AP1000';
            $service->spreadsheets_values->update($sheet, $range, $body, $params);
        }


        // Group $studentsFiltered by sheet and update with 'text' value

        $groupedStudents = [];
        foreach ($studentsFiltered as $student) {
            if (!isset($groupedStudents[$student['sheet_id']])) {
                $groupedStudents[$student['sheet_id']] = [];
            }
            $groupedStudents[$student['sheet_id']][] = $student;
        }

        // Recorrer cada hoja de cálculo
        foreach ($sheets as $sheet) {
            if (isset($groupedStudents[$sheet])) {
                // Recorrer cada estudiante en esta hoja
                foreach ($groupedStudents[$sheet] as $student) {
                    $rowNumber = $student['course_row_number'];                      // Obtener el número de fila
                    $message   = $student['free_courses_weighted_msg'][0]['text'];  // Obtener el mensaje a enviar
                    $range     = 'CURSOS!AP' . $rowNumber;                           // Definir el rango, en este caso columna AP

                    // Crear un cuerpo con los valores a actualizar
                    $body = new Google_Service_Sheets_ValueRange([
                        'values' => [[$message]]
                    ]);

                    // Actualizar la hoja de cálculo
                    $service->spreadsheets_values->update($sheet, $range, $body, $params);
                }
            }
        }
    }
}
