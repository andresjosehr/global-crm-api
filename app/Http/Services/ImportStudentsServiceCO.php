<?php

namespace App\Http\Services;

use App\Console\Commands\Processes\UpdateTestsStatus;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\Sheet;
use App\Models\Student;
use App\Models\User;
use App\Models\Wordpress\WpLearnpressUserItem;
use App\Models\Wordpress\WpUser;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Exception;
use GuzzleHttp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Google_Client;
use Google_Service_Sheets;

class ImportStudentsServiceCO
{

    private $service;

    private $badCourses = [
        "PB"                   => "POWERBI BASICO",
        "POWER BI"             => "POWERBI BASICO",
        "MSPJ"                 => 'MS PROJECT',
        "MS"                   => 'MS PROJECT',
        "MSP"                  => 'MS PROJECT',
        "POWERBI"              => 'POWERBI BASICO',
        "MSPROJ"               => 'MS PROJECT',
        "EXCL"                 => 'EXCEL',
        "POWRB"                => 'POWERBI BASICO',
        "FI"                   => 'SAP FI',
        "MSPROJECT"            => 'MS PROJECT',
        // 'SAP MM EXCEL'         => 'SAP MM',
        "EXCELL"               => 'EXCEL',
        "MS PRJ"               => 'MS PROJECT',
        "POWER BI AVANZADO"    => 'POWERBI AVANZADO',
        // "SAP MM (PROMO) EXCEL" => 'SAP MM',
        "EXCEL EMPRESARIAL"    => 'EXCEL',
        "PP"                   => 'SAP PP',
        "MS PROJEC"            => 'MS PROJECT',
        "SAP  MM"              => 'SAP MM',
        "POWER BI  AVANZADO"   => 'POWERBI AVANZADO',
        // "SAP ABAP"             => 'SAP PP',
        "POWER BI AVANZANDO"   => 'POWERBI AVANZADO',
        // "SAP HCM EXCEL"        => 'SAP HCM',
        'POWER'                => 'POWERBI BASICO',
        'MS PROJ'              => 'MS PROJECT',
        // 'EXCEL POWERBI'        => 'EXCEL',
        // 'SAP PM EXCEL'         => 'SAP PM',
    ];


    public function index($sheet_type = 'test')
    {

        // dermtz02@gmail.com
        // memory_limit
        ini_set('memory_limit', '2048M');
        // set_time_limit
        set_time_limit(0);

        self::createGoogleServiceInstance();
        $data = self::getSheetsData($sheet_type);
        $data = self::formatCourses($data);;

        $data = self::formatForImport($data);
        $data = self::import($data);

        return ['Exito'];
    }


    public function import($data)
    {
        $allStudents = Student::all()->pluck('id', 'email')->toArray();
        foreach ($data as $student) {

            // If student exists by email, do not create a new user
            if (isset($allStudents[$student['email']])) {
                continue;
            }


            $studentDB = Student::create($student);

            $student['order']['student_id'] = $studentDB->id;
            $order = Order::create($student['order']);

            foreach ($student['order']['order_courses'] as $course) {
                $course['order_id'] = $order->id;
                $orderCourse = OrderCourse::create($course);
            }
        }
    }


    public function formatForImport($data)
    {
        $users = User::all()->pluck('id', 'name')->toArray();
        $created_by = User::where('email', 'asesor.prueba@gmail.com')->first()->id;
        $students = [];
        foreach ($data as $i => $student) {
            $studentData = [
                'name'           => $student['NOMBRE'],
                'email'          => $student['CORREO'],
                'phone'          => $student['TELÉFONO'],
                'document'       => $student['DOCUMENTO'],
                'classroom_user' => isset($student[$student['USUARIO_AULA_ULTIMATE']]) ? $student[$student['USUARIO_AULA_ULTIMATE']] : null,
                'user_id'        => $student['wp_user_id'],
                'user_id'        => isset($users[$student['COMP']]) ? $users[$student['COMP']] : null,
                'created_by'     => $created_by,
                'created_at'     => '2024-01-01 00:00:00',
                'updated_at'     => '2024-01-01 00:00:00',
                'order'          => [
                    'payment_mode'               => 'X',
                    'price_amount'               => 0,
                    'sap_notes'                  => 'INSTALACIÓN: ' . $student['INSTALACIÓN'] . " -------- " . 'Notas: ' . $student['sap_notes'],
                    'terms_confirmed_by_student' => 1,
                    'order_courses'              => [],
                    'observation'                => 'Notas: ' . $student['start_notes'],
                    'created_at'                 => '2024-01-01 00:00:00',
                    'updated_at'                 => '2024-01-01 00:00:00',
                ]
            ];

            $coursesDB = DB::table('courses')->get()->pluck('type', 'id')->toArray();
            $orderCourses = [];
            foreach ($student['courses'] as $j => $course) {
                $courseData = [
                    'course_id'        => $course['course_id'],
                    'classroom_status' => $course['status'] ? $course['status'] : '',
                    'license'          =>  strtolower($student['LICENCIA Y AULA VIRTUAL']),
                    'type'             => $coursesDB[$course['course_id']],
                    'start'            => $course['start'],
                    'end'              => $course['end'],
                    'enabled'          => 1,
                    'created_at'       => '2024-01-01 00:00:00',
                    'updated_at'       => '2024-01-01 00:00:00',
                ];
                $orderCourses[] = $courseData;
            }

            $studentData['order']['order_courses'] = $orderCourses;

            $students[] = $studentData;
        }
        return $students;
    }

    public function formatCourses($data)
    {

        $users_db = WpUser::select('ID', 'user_email')
            ->get()
            ->mapWithKeys(function ($user) {
                return [strtolower($user->user_email) => $user->ID];
            })
            ->toArray();
        $users_db2 = WpUser::select('ID', 'user_login')->get()->pluck('ID', 'user_login')->toArray();

        $courses_not_found = [];
        $studentsFromCoursesNotFound = [];
        foreach ($data as $i => $student) {
            $student['CORREO'] = strtolower($student['CORREO']);
            $data[$i]['wp_user_id'] = isset($users_db[$student['CORREO']]) ? $users_db[$student['CORREO']] : null;


            $col = '';
            if ($data[$i]['USUARIO AULA']) {
                $col = 'USUARIO AULA';
            }



            $data[$i]['USUARIO_AULA_ULTIMATE'] = $col;
            if (!$data[$i]['wp_user_id']) {
                if ($col) {
                    $data[$i]['wp_user_id'] = isset($users_db2[$student[$col]]) ? $users_db2[$student[$col]] : null;
                    $data[$i]['USUARIO_AULA_ULTIMATE'] = $student[$col];
                }

                if (!$data[$i]['wp_user_id']) {
                    $data[$i]['wp_user_id'] = isset($users_db[$student['CORREO']]) ? $users_db[$student['CORREO']] : null;
                }
            }

            $courses_names = explode('+', $student['CURSOS']);
            $courses_names = array_map('trim', $courses_names);
            $courses_names = array_map('strtoupper', $courses_names);


            $courses = [];
            $inactive_courses = [];

            $sapNumber = 0;
            $unicSap = '';
            foreach ($courses_names as $course_name) {
                if (in_array($course_name, ['PP', 'MM', 'PM', 'HCM', 'FI', 'INTEGRAL', 'SAP PP', 'SAP MM', 'SAP PM', 'SAP HCM', 'SAP FI', 'SAP INTEGRAL'])) {
                    $unicSap = $course_name;
                    $sapNumber++;
                }
            }

            $coursesStatus = [];

            if ($sapNumber > 1) {

                $statusCol = $student['ESTADO'];

                $statues =  [
                    ['key' => 'HABILITAR', 'status' => 'Cursando'],
                    ['key' => 'HABILITADO', 'status' => 'Cursando'],
                    ['key' => 'CONTADO', 'status' => 'Cursando'],
                    ['key' => 'PENDIENTE', 'status' => 'Por habilitar']
                ];


                foreach ($statues as $statue) {
                    if (strpos($statusCol, $statue['key']) !== false) {
                        $part = explode($statue['key'], $statusCol)[1];
                        $part = explode('/', $part)[0];
                        foreach ($par = explode(' ', $part) as $p) {
                            if ($p != '') {
                                $coursesStatus["SAP $p"] = $statue['status'];
                            }
                        }
                    }
                }
            } else {
                $coursesStatus[$unicSap] = $student['USUARIO AULA'];
            }



            $free_courses = [];
            $sap_courses = [];
            foreach ($courses_names as $course_name) {

                if (in_array($course_name, ['PP', 'MM', 'PM', 'HCM', 'FI', 'INTEGRAL'])) {
                    $course_name = 'SAP ' . $course_name;
                }

                if (!$course_db = DB::table('courses')->where('short_name', $course_name)->when(isset($this->badCourses[$course_name]), function ($q) use ($course_name) {
                    return $q->orWhere('short_name', $this->badCourses[$course_name]);
                })->first()) {

                    $courses_not_found[] = $course_name;
                    $studentsFromCoursesNotFound[] = $student;
                    $studentsFromCoursesNotFound[count($studentsFromCoursesNotFound) - 1]['course_not_found'] = $course_name;
                    continue;
                }


                $order_id = null;
                if ($order = WpLearnpressUserItem::select('ref_id')->where('user_id', $data[$i]['wp_user_id'])->where('item_id', $course_db->wp_post_id)->first()) {
                    $order_id = $order->ref_id;
                }

                if (strpos($course_name, 'SAP') !== false) {



                    $sap_courses[] = [
                        'course_id'                  => $course_db->id,
                        'sap_user'                   => $student['USUARIO SAP'],
                        'name'                       => $course_db->name,
                        'access'                     => $student['ACCESOS'],
                        'course_status_original'     => $student['USUARIO AULA'],
                        'status'                     => isset($coursesStatus[$course_name]) ? ucwords(strtolower($coursesStatus[$course_name])) : null,
                        'start'                      => null,
                        'end'                        => null,
                        'order_id'                   => $order_id,
                        'wp_post_id'                 => $course_db->wp_post_id,
                        'type'                       => 'paid'
                    ];

                    if (!isset($coursesStatus[$course_name])) {
                        $s = 'Por habilitar';
                    } else {
                        $s = $coursesStatus[$course_name];
                    }


                    if ($sapNumber == 1 || $s == 'Cursando') {
                        $start = $student['INICIO'];
                        $end = $student['FIN'];

                        try {
                            $start = self::__parseDate($start);
                            $end = self::__parseDate($end);
                        } catch (\Throwable $th) {

                            $start = null;
                            $end   = null;
                        }

                        $sap_courses[count($sap_courses) - 1]['start'] = $start;
                        $sap_courses[count($sap_courses) - 1]['end'] = $end;
                    }
                }

                if (strpos($course_name, 'SAP') === false) {




                    $free_courses[] = [
                        'course_id'  => $course_db->id,
                        'sap_user'   => $student['USUARIO SAP'],
                        'name'       => $course_db->name,
                        'start'      => null,
                        'end'        => null,
                        'status'     => null,
                        'order_id'   => $order_id,
                        'wp_post_id' => $course_db->wp_post_id,
                        'type'       => 'free'
                    ];
                }
            }

            $data[$i]['courses'] = [...$sap_courses, ...$free_courses];
        }




        return $data;
    }

    public function formatProgress($data)
    {


        // Realizar la consulta
        $lessons = DB::connection('wordpress')->table('posts as lessons')
            ->select('lessons.*', 'sections.section_course_id', 'sections.section_name')
            ->join('learnpress_section_items as section_items', 'section_items.item_id', '=', 'lessons.ID')
            ->join('learnpress_sections as sections', 'sections.section_id', '=', 'section_items.section_id')
            ->where('lessons.post_type', 'lp_lesson')
            ->where('lessons.post_title', 'not like', '%webinar%')
            ->orderBy('sections.section_course_id')
            ->orderBy('sections.section_name')
            ->orderBy('section_items.item_order', 'ASC')
            ->get()->unique('ID')->values();

        // Agrupar las lecciones por el ID del curso
        $groupedLessons = $lessons->groupBy('section_course_id');

        $groupedLessons['11148'] = $groupedLessons['11148']->groupBy('section_name');

        // return $groupedLessons['11148']['Excel Básico (Nivel I)']->pluck('ID')->toArray();



        // Count lessons by course
        $lessonsCount = [];
        $lessons = [];
        foreach ($groupedLessons as $course_id => $course_lessons) {
            $lessonsCount[$course_id] = $course_lessons->count();
            $lessons[$course_id] = $course_lessons->pluck('ID')->toArray();
        }



        // Realizar la consulta
        $quizzes = DB::connection('wordpress')->table('posts as quizzes')
            ->select('quizzes.ID', 'sections.section_course_id')
            ->join('learnpress_section_items as section_items', 'section_items.item_id', '=', 'quizzes.ID')
            ->join('learnpress_sections as sections', 'sections.section_id', '=', 'section_items.section_id')
            ->join('postmeta as pm', function ($join) {
                $join->on('pm.post_id', '=', 'quizzes.ID')
                    ->where('pm.meta_key', '=', 'examen_de_certificacion')
                    ->where('pm.meta_value', '=', '1');
            })
            ->where('quizzes.post_type', 'lp_quiz')
            ->where('quizzes.post_title', 'like', '%CERTIFICACION%')
            ->orderBy('sections.section_course_id')
            ->orderBy('section_items.item_order', 'ASC')
            ->get();

        // Agrupar los quizzes por el ID del curso
        $groupedQuizzes = $quizzes->groupBy('section_course_id');

        $excelQuizzes                     = [];
        $excelQuizzes['nivel_basico']     = [$groupedQuizzes['11148'][0]->ID, $groupedQuizzes['11148'][1]->ID, $groupedQuizzes['11148'][2]->ID];
        $excelQuizzes['nivel_intermedio'] = [$groupedQuizzes['11148'][3]->ID, $groupedQuizzes['11148'][4]->ID, $groupedQuizzes['11148'][5]->ID];
        $excelQuizzes['nivel_avanzado']   = [$groupedQuizzes['11148'][6]->ID, $groupedQuizzes['11148'][7]->ID, $groupedQuizzes['11148'][8]->ID];
        // return $excelQuizzes;




        foreach ($data as $i => $student) {



            $certificates = [
                'nivel_basico'     => 'EXC CERTIF. BÁS',
                'nivel_intermedio' => 'EXC CERTIF. INT',
                'nivel_avanzado'   => 'EXC CERTIF. AVA',
            ];
            foreach ($student['courses'] as $j => $course) {
                if ($course['course_id'] != 6) {
                    $lessons_completed = WpLearnpressUserItem::where('user_id', $data[$i]['wp_user_id'])
                        ->where('ref_id', $course['wp_post_id'])
                        ->where('item_type', 'lp_lesson')
                        ->where('status', 'completed')
                        ->whereHas('item', function ($q) {
                            $q->where('post_title', 'not like', '%webinar%');
                        })
                        ->get()->unique('item_id')->values();

                    $quizzes = WpLearnpressUserItem::where('user_id', $data[$i]['wp_user_id'])
                        ->where('ref_id', $course['wp_post_id'])
                        ->where('item_type', 'lp_quiz')
                        ->whereIn('item_id', $groupedQuizzes[$course['wp_post_id']]->pluck('ID')->toArray())
                        // ->where('status', 'completed')
                        ->get();

                    $data[$i]['courses'][$j]['lessons_count'] = $lessonsCount[$course['wp_post_id']];
                    $data[$i]['courses'][$j]['lessons_completed'] = $lessons_completed->count();




                    if ($data[$i]['courses'][$j]['lessons_completed'] >= 0) {
                        $data[$i]['courses'][$j]['lesson_progress'] = 'EN PROGRESO';
                    }

                    if ($data[$i]['courses'][$j]['lessons_completed'] == $data[$i]['courses'][$j]['lessons_count']) {
                        $data[$i]['courses'][$j]['lesson_progress'] = 'COMPLETADO';
                    }


                    $now = Carbon::now()->setTimezone('America/Lima');
                    $start = Carbon::parse($course['start'])->setTimezone('America/Lima');
                    $end = Carbon::parse($course['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);

                    if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                        $data[$i]['courses'][$j]['course_status'] = $data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'CURSANDO';
                    } elseif ($now->greaterThan($end)) {
                        $data[$i]['courses'][$j]['course_status'] = $data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'NO CULMINÓ';
                    } elseif ($now->lessThan($start)) {
                        $data[$i]['courses'][$j]['course_status'] = 'POR HABILITAR';
                    }

                    if ($course['start'] == null && $course['end'] == null) {
                        $data[$i]['courses'][$j]['course_status'] = 'POR HABILITAR';
                    }

                    if ($data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO') {
                        $data[$i]['courses'][$j]['course_status'] = 'COMPLETA';
                    }


                    $data[$i]['courses'][$j]['certifaction_test'] = $quizzes->filter(function ($quiz) {
                        return $quiz->graduation == 'passed';
                    })->count() == 0 ? null : 'Aprobado';



                    if ($data[$i]['courses'][$j]['certifaction_test'] == null) {
                        if (count($quizzes) == 0) {
                            $data[$i]['courses'][$j]['certifaction_test'] = "3 Intentos pendientes";
                        } elseif (count($quizzes) == 1) {
                            $data[$i]['courses'][$j]['certifaction_test'] = "2 Intentos pendientes";
                        } elseif (count($quizzes) == 2) {
                            $data[$i]['courses'][$j]['certifaction_test'] = "1 Intento pendiente";
                        } elseif (count($quizzes) == 3) {
                            $data[$i]['courses'][$j]['certifaction_test'] = "Sin Intentos Gratis";
                        } else {
                            $data[$i]['courses'][$j]['certifaction_test'] = "Sin intentos Gratis";
                        }
                    }


                    if ($now->lessThanOrEqualTo($start) && $course['start'] != null) {
                        $data[$i]['courses'][$j]['certifaction_test'] = '3 Intentos pendientes';
                    }

                    $data[$i]['courses'][$j]['quizzes'] = $quizzes;
                }

                if ($course['course_id'] == 6) {
                    $levels = ['nivel_basico' => 'Excel Básico (Nivel I)', 'nivel_intermedio' => 'Excel Intermedio (Nivel II)', 'nivel_avanzado' => 'Excel Avanzado (Nivel III)'];
                    $certificationTestCols = ['nivel_basico' => 'EXC EXAMEN BÁS.', 'nivel_intermedio' => 'EXC EXAMEN INT.', 'nivel_avanzado' => 'EXC EXAMEN AVA.'];
                    foreach ($levels as $key => $name) {

                        $data[$i]['courses'][$j][$key]                      = [];                                                   // Inicialización aquí
                        $data[$i]['courses'][$j][$key]['certificate']       = $student[$certificates[$key]];
                        $data[$i]['courses'][$j][$key]['certifaction_test_original'] = $student[$certificationTestCols[$key]];

                        $lessons_completed = WpLearnpressUserItem::where('user_id', $data[$i]['wp_user_id'])
                            ->where('ref_id', $course['wp_post_id'])
                            ->where('item_type', 'lp_lesson')
                            ->where('status', 'completed')
                            ->whereIn('item_id', $groupedLessons['11148'][$name]->pluck('ID')->toArray())
                            // Unique by item_id
                            ->get()->unique('item_id')->values();

                        $data[$i]['courses'][$j][$key]['lessons_count']     = $groupedLessons['11148'][$name]->count();
                        $data[$i]['courses'][$j][$key]['lessons_completed'] = $lessons_completed->count();



                        if ($data[$i]['courses'][$j][$key]['lessons_completed'] >= 0) {
                            $data[$i]['courses'][$j][$key]['lesson_progress'] = 'EN PROGRESO';
                        }

                        if ($data[$i]['courses'][$j][$key]['lessons_completed'] == $groupedLessons['11148'][$name]->count()) {
                            $data[$i]['courses'][$j][$key]['lesson_progress'] = 'COMPLETADO';
                        }


                        $now   = Carbon::now()->setTimezone('America/Lima');
                        $start = Carbon::parse($course['start'])->setTimezone('America/Lima');
                        $end   = Carbon::parse($course['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);

                        if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                            $data[$i]['courses'][$j][$key]['course_status'] = $data[$i]['courses'][$j][$key]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'CURSANDO';
                        } else {
                            $data[$i]['courses'][$j][$key]['course_status'] = $data[$i]['courses'][$j][$key]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'NO CULMINÓ';
                        }



                        // new collection
                        $quizzes = WpLearnpressUserItem::where('user_id', $data[$i]['wp_user_id'])
                            ->where('ref_id', $course['wp_post_id'])
                            ->where('item_type', 'lp_quiz')
                            ->whereIn('item_id', $excelQuizzes[$key])
                            // ->where('status', 'completed')
                            ->get();

                        $data[$i]['courses'][$j][$key]['certifaction_test'] = $quizzes->filter(function ($quiz) {
                            return $quiz->graduation == 'passed';
                        })->count() == 0 ? null : 'Aprobado';

                        if ($data[$i]['courses'][$j][$key]['certifaction_test'] == null) {
                            if (count($quizzes) == 0) {
                                $data[$i]['courses'][$j][$key]['certifaction_test'] = "3 Intentos pendientes";
                            } elseif (count($quizzes) == 1) {
                                $data[$i]['courses'][$j][$key]['certifaction_test'] = "2 Intentos pendientes";
                            } elseif (count($quizzes) == 2) {
                                $data[$i]['courses'][$j][$key]['certifaction_test'] = "1 Intento pendiente";
                            } elseif (count($quizzes) == 3) {
                                $data[$i]['courses'][$j][$key]['certifaction_test'] = "Sin Intentos Gratis";
                            } else {
                                $data[$i]['courses'][$j][$key]['certifaction_test'] = "Sin intentos Gratis";
                            }
                        }
                    }

                    // Sum all lessons completed
                    $data[$i]['courses'][$j]['lessons_completed'] = $data[$i]['courses'][$j]['nivel_basico']['lessons_completed'] + $data[$i]['courses'][$j]['nivel_intermedio']['lessons_completed'] + $data[$i]['courses'][$j]['nivel_avanzado']['lessons_completed'];

                    // sum all lessons count
                    $data[$i]['courses'][$j]['lessons_count'] = $data[$i]['courses'][$j]['nivel_basico']['lessons_count'] + $data[$i]['courses'][$j]['nivel_intermedio']['lessons_count'] + $data[$i]['courses'][$j]['nivel_avanzado']['lessons_count'];

                    if ($data[$i]['courses'][$j]['lessons_completed'] >= 0) {
                        $data[$i]['courses'][$j]['lesson_progress'] = 'EN PROGRESO';
                    }

                    if ($data[$i]['courses'][$j]['lessons_completed'] == $data[$i]['courses'][$j]['lessons_count']) {
                        $data[$i]['courses'][$j]['lesson_progress'] = 'COMPLETADO';
                    }


                    if ($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end)) {
                        $data[$i]['courses'][$j]['course_status'] = $data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'CURSANDO';
                    } elseif ($now->greaterThan($end)) {
                        $data[$i]['courses'][$j]['course_status'] = $data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO' ? 'COMPLETA' : 'NO CULMINÓ';
                    } elseif ($now->lessThan($start)) {
                        $data[$i]['courses'][$j]['course_status'] = 'POR HABILITAR';
                    } elseif (!$course['start'] && !$course['end']) {
                        $data[$i]['courses'][$j]['course_status'] = '';
                    }

                    if ($data[$i]['courses'][$j]['lesson_progress'] == 'COMPLETADO') {
                        $data[$i]['courses'][$j]['course_status'] = 'COMPLETA';
                    }

                    if (!($now->greaterThanOrEqualTo($start) && $now->lessThanOrEqualTo($end))) {
                        $data[$i]['courses'][$j]['certifaction_test'] = '';
                    }
                }
            }
        }

        return $data;
    }

    public function attachCertificacionTestStatus($data)
    {
        $command = new UpdateTestsStatus();
        $data = $command->handle($data, true);
        return $data;
    }

    public function getSheetsData($sheet_type = 'test')
    {
        $sheet = '1AR45Qf8_QU5L5DJ39WFpb5fK5jz6CL9WXap1KbBGHhk';


        $data = [];
        $ranges = ['COBRANZA BASE!A1:ZZZ50000', 'COBRANZA CURSOS!A1:ZZZ50000'];

        $response = $this->service->spreadsheets_values->batchGet($sheet, ['ranges' => $ranges]);

        $noteResponse = $this->service->spreadsheets->get($sheet, [
            'ranges' => $ranges,
            'includeGridData' => true, // Esto es necesario para obtener las notas
            'fields' => 'sheets(data(rowData(values(note))))' // Especificar solo los campos necesarios
        ]);


        $baseSheet = $response[0]->getValues();
        $coursesSheet = $response[1]->getValues();

        $baseHeaders = $baseSheet[0];
        $baseData = [];
        array_shift($baseSheet); // Remove headers row (first row)

        $baseRowNumber = 1;  // Initialize counter for base rows
        foreach ($baseSheet as $row) {
            $row = array_slice($row, 0, count($baseHeaders));
            while (count($row) < count($baseHeaders)) {
                $row[] = null;
            }
            $baseData[] = array_merge(['row_number' => $baseRowNumber + 1], array_combine($baseHeaders, $row));  // Add row number and set headers as keys for each row
            $baseRowNumber++;  // Increment the counter
        }

        $coursesHeaders = $coursesSheet[0];
        $coursesData = [];
        array_shift($coursesSheet); // Remove headers row (first row)

        $courseRowNumber = 1;  // Initialize counter for course rows
        foreach ($coursesSheet as $row) {
            $row = array_slice($row, 0, count($coursesHeaders));
            while (count($row) < count($coursesHeaders)) {
                $row[] = null;
            }
            $coursesData[] = array_merge(['row_number' => $courseRowNumber + 1], array_combine($coursesHeaders, $row));  // Add row number and set headers as keys for each row
            $courseRowNumber++;  // Increment the counter
        }

        $i = -1;
        foreach ($baseData as $baseRow) {
            $email = $baseRow['CORREO'];
            $email = strtolower($email);
            $email = trim($email);
            $email = str_replace(' ', '', $email);


            if ($email) {
                foreach ($coursesData as $courseRow) {
                    $courseRow['CORREO'] = strtolower($courseRow['CORREO']);
                    $courseRow['CORREO'] = trim($courseRow['CORREO']);
                    $courseRow['CORREO'] = str_replace(' ', '', $courseRow['CORREO']);
                    if ($email == $courseRow['CORREO']) {
                        // Merge base and course rows for the same 'CORREO'
                        $mergedRow                      = array_merge($baseRow, $courseRow);
                        $mergedRow['base_row_number']   = $baseRow['row_number'];
                        $mergedRow['course_row_number'] = $courseRow['row_number'];
                        try {
                            $mergedRow['sap_notes'] = $noteResponse->getSheets()[1]->getData()[0]->getRowData()[$i]->getValues()[7]->getNote();
                        } catch (\Throwable $th) {
                            $mergedRow['sap_notes'] = null;
                        }

                        try {
                            $mergedRow['start_notes'] = $noteResponse->getSheets()[1]->getData()[0]->getRowData()[$i]->getValues()[8]->getNote();
                        } catch (\Throwable $th) {
                            $mergedRow['start_notes'] = null;
                        }


                        // $mergedRow['sheet_id']          = $sheet->sheet_id;
                        // $mergedRow['course_tab_id']     = $sheet->course_tab_id;
                        // $mergedRow['base_tab_id']       = $sheet->base_tab_id;
                        $data[]                         = $mergedRow;
                    }
                }
            }
            $i++;
        }


        return $data;
    }
    public function createGoogleServiceInstance()
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

        return $this->service = new Google_Service_Sheets($client);
    }

    /**
     * Formatea una fecha en formato d/m/Y o Y-m-d a Y-m-d
     * @param string $date Fecha en formato "d/m/Y" o "Y-m-d"
     */
    private static function  __parseDate($date)
    {
        if (empty($date) == false && strpos($date, '/') !== false) :
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        elseif (empty($date) == false && strpos($date, '-') !== false) :
            return Carbon::parse($date)->format('Y-m-d');
        else :
            return null;
        endif;
    }
}
