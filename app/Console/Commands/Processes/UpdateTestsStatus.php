<?php

namespace App\Console\Commands\Processes;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateTestsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-tests-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

                // if (in_array($student[$column], $status)) {
                //     // remove course with id 6
                //     $courses = array_filter($student['courses'], function ($course) use ($id) {
                //         return $course['course_id'] != $id;
                //     });

                //     $courses = array_values($courses);
                //     $student['courses'] = $courses;
                //     continue;
                // }
            }

            // replace course in courses
            $student['courses'] = array_map(function ($c) {

                $now = Carbon::now()->setTimezone('America/Lima');
                $end = Carbon::parse($c['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);


                $c['_greaterThan'] = $now->greaterThan($end);
                $c['_now']         = $now->format('Y-m-d');
                $c['_end']         = $end->format('Y-m-d');
                if (($now->greaterThan($end) || $c['end'] == null) && ($c['certifaction_test'] == '2 Intentos pendientes' || $c['certifaction_test'] == '1 Intento pendiente' || $c['certifaction_test'] == 'Sin Intentos Gratis')) {
                    $c['certifaction_test'] = 'Reprobado';
                }
                if (($now->greaterThan($end)) && $c['certifaction_test'] == '3 Intentos pendientes') {
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

                    if ($c['nivel_basico']['certificate'] == 'EMITIDO') {
                        $c['nivel_basico']['certifaction_test'] = 'Aprobado';
                    }
                    if ($c['nivel_intermedio']['certificate'] == 'EMITIDO') {
                        $c['nivel_intermedio']['certifaction_test'] = 'Aprobado';
                    }
                    if ($c['nivel_avanzado']['certificate'] == 'EMITIDO') {
                        $c['nivel_avanzado']['certifaction_test'] = 'Aprobado';
                    }


                    if (!$c['end'] && !$c['start']) {


                        if(!$c['nivel_basico']['certificate']){ $c['nivel_basico']['certifaction_test'] = '3 Intentos pendientes';}
                        if(!$c['nivel_intermedio']['certificate']){ $c['nivel_intermedio']['certifaction_test'] = '3 Intentos pendientes';}
                        if(!$c['nivel_avanzado']['certificate']){ $c['nivel_avanzado']['certifaction_test'] = '3 Intentos pendientes';}
                    }
                }

                if ($c['course_id'] != 6) {
                    if ($c['certificate'] == 'EMITIDO') {
                        $c['certifaction_test'] = 'Aprobado';
                    }
                }

                return $c;
            }, $student['courses']);

            return $student;
        }, $students);


        $studentsFitered = array_filter($studentsFitered, function ($student) {
            return count($student['courses']) > 0 ;
        });
        $studentsFitered = array_values($studentsFitered);


        // return $studentsFitered;


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

        return $this->line(json_encode(["Exito" => $studentsFitered]));
        return Command::SUCCESS;
    }
}
