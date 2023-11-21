<?php

namespace App\Console\Commands\Processes;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Course;
use App\Models\Wordpress\WpPost;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateCoursesStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-courses-status';

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

        $data = new StudentsExcelController();
        $students = $data->index('prod');
        // return $this->line(json_encode($students));

        $studentsFitered = array_map(function ($student) {
            // if (!$student['wp_user_id']) {
            //     return $student;
            // }



            $course_status_columns = [1 => 'AULA SAP', 2 => 'AULA SAP', 3 => 'AULA SAP', 4 => 'AULA SAP', 5 => 'AULA SAP', 10 => 'AULA SAP', 6 => 'EXCEL', 7 => 'PBI', 8 => 'PBI' ,9 => 'MS PROJECT'];
            $courses = array_filter($student['courses'], function ($course) use ($course_status_columns, $student) {
                if($student[$course_status_columns[$course['course_id']]] == 'ABANDONÓ'){
                    return false;
                }
                return true;
            });

            $courses = array_values($courses);



            $col = [1 => 'CERTIFICADO', 2 => 'CERTIFICADO', 3 => 'CERTIFICADO', 4 => 'CERTIFICADO', 5 => 'CERTIFICADO', 10 => 'CERTIFICADO', 6 => 'EXC CERTIF. AVA', 7 => 'PBI CERTIFICADO', 8 => 'PBI CERTIFICADO', 9 => 'MSP CERTIFICADO'];
            $courses = array_map(function ($course) use ($student, $col) {
                if (!$course['end'] && !$course['start']) {
                    $id = $course['course_id'];

                    $course['course_status'] = 'POR HABILITAR';

                    if ($course['certificate'] == 'NO APLICA') {
                        $course['course_status'] = 'NO CULMINÓ';
                    }
                    if ($course['certificate'] == 'EMITIDO') {
                        $course['course_status'] = 'COMPLETA';
                    }
                }


                $now = Carbon::now()->setTimezone('America/Lima');
                $start = Carbon::parse($course['start'])->setTimezone('America/Lima');
                $end = Carbon::parse($course['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);

                if ($course['type'] == 'paid' && $student['ACCESOS'] == 'CORREO CONGELAR') {
                    if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                        $course['course_status'] = 'CURSANDO';
                    }
                    if ($now->lessThan($start)) {
                        $course['course_status'] = 'POR HABILITAR';
                    }
                }

                return $course;
            }, $courses);

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

                if ($course['course_id'] == 6) {
                    if ($course['nivel_basico']['certificate'] == "NO APLICA" || $course['nivel_intermedio']['certificate'] == "NO APLICA" || $course['nivel_avanzado']['certificate'] == "NO APLICA") {
                        $course['course_status'] = 'NO CULMINÓ';
                    }
                }

                return $course;
            }, $freeCourses);


            $student['courses'] = array_merge($paidCourses, $freeCourses);

            return $student;
        }, $students);


        $studentsFitered = array_map(function ($student) {
            if (!$student['wp_user_id']) {
                $student['courses'] = array_map(function ($course) {
                    if ($course['certificate'] == '') {
                        $course['course_status'] = 'POR HABILITAR';
                    }
                    return $course;
                }, $student['courses']);
            }
            return $student;
        }, $studentsFitered);


        $paidCourseDB = Course::where('type', 'paid')->pluck('id')->toArray();
        $freeCoursesDB = Course::where('type', 'free')->whereNotIn('id', [7, 8])->get()->pluck('id')->toArray();

        $studentsFitered = array_map(function ($student) use ($paidCourseDB, $freeCoursesDB) {
            $courseIds = array_column($student['courses'], 'course_id');


            if (!in_array(6, $courseIds)) {
                $student['courses'][] = [
                    'course_id' => 6,
                    'name' => "EXCEL",
                    'type' => 'free',
                    'course_status' => 'NO APLICA',
                ];
            }
            if (!in_array(9, $courseIds)) {
                $student['courses'][] = [
                    'course_id' => 9,
                    'name' => "MS PROJECT",
                    'type' => 'free',
                    'course_status' => 'NO APLICA',
                ];
            }

            if (!in_array(7, $courseIds) && !in_array(8, $courseIds)) {
                $student['courses'][] = [
                    'course_id' => 7,
                    'name' => 'POWERBI',
                    'type' => 'free',
                    'course_status' => 'NO APLICA',
                ];
            }

            if (empty(array_intersect($courseIds, $paidCourseDB))) {
                $student['courses'][] = [
                    'course_id' => 0,
                    'name' => 'GENERAL SAP',
                    'type' => 'paid',
                    'course_status' => 'NO APLICA',
                ];

                // Ninguno de los cursos del usuario es un curso pagado
            }


            return $student;
        }, $studentsFitered);

        $studentsFitered = array_values($studentsFitered);

        // return $this->line(json_encode($studentsFitered));


        $studentsFitered = array_map(function ($student) {
            $courses = array_map(function ($course) {
                if ($course['course_status'] == 'POR HABILITAR' && $course['order_id'] && !$course['start'] && !$course['end']) {
                    $order_date = WpPost::where('ID', $course['order_id'])->first()->post_date;
                    // Check if date is gratter than 2 months
                    $now = Carbon::now()->setTimezone('America/Lima');
                    $order_date = Carbon::parse($order_date)->setTimezone('America/Lima');
                    $diff = $now->diffInMonths($order_date);
                    if($diff >= 2){
                        $course['order_date'] = $order_date;
                        $course['diff'] = $diff;

                        if($course['lesson_progress']=='EN PROGRESO'){
                            $course['course_status'] = 'NO CULMINÓ';
                        }
                        if($course['lesson_progress']=='COMPLETADO'){
                            $course['course_status'] = 'COMPLETA';
                        }

                    }
                }
                return $course;
            }, $student['courses']);

            $student['courses'] = $courses;
            return $student;
        }, $studentsFitered);

        // return $this->line(json_encode($studentsFitered));

        $studentsFitered = array_map(function ($student){
            $student['courses'] = array_map(function ($course){
                if($course['course_id'] == 8){
                    if($course['course_status'] == 'POR HABILITAR'){
                        $course['course_status'] = 'POR HABILITAR AVANZADO';
                    }
                    if($course['course_status'] == 'CURSANDO'){
                        $course['course_status'] = 'CURSANDO AVANZADO';
                    }
                    if($course['course_status'] == 'CURSANDO SIN CREDLY'){
                        $course['course_status'] = 'CURSANDO AVANZADO SIN CREDLY';
                    }
                    if($course['course_status'] == 'CURSANDO'){
                        $course['course_status'] = 'CURSANDO AVANZADO';
                    }
                }
                return $course;
            }, $student['courses']);
            return $student;
        }, $studentsFitered);

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
        $dataToUpdate = $data;
        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        // // sbasurto686@gmail.com
        // $sbasurto = array_filter($studentsFitered, function ($student) {
        //     return $student['CORREO'] == 'sbasurto686@gmail.com';
        // });
        return $this->line(json_encode(["Exito" => $studentsFitered]));
    }
}

// Eso es en una computadora con tu procesador?
