<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Models\Sheet;
use App\Models\Wordpress\WpLearnpressUserItem;
use App\Models\Wordpress\WpUser;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Google_Client;
use Google_Service_Sheets;


class StudentsExcelController extends Controller
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
        'SAP MM EXCEL'         => 'SAP MM',
        "EXCELL"               => 'EXCEL',
        "MS PRJ"               => 'MS PROJECT',
        "POWER BI AVANZADO"    => 'POWERBI AVANZADO',
        "SAP MM (PROMO) EXCEL" => 'SAP MM',
        "EXCEL EMPRESARIAL"    => 'EXCEL',
        "PP"                   => 'SAP PP',
        "MS PROJEC"            => 'MS PROJECT',
        "SAP  MM"              => 'SAP MM',
        "POWER BI  AVANZADO"   => 'POWERBI AVANZADO',
        "SAP ABAP"             => 'SAP PP',
        "POWER BI AVANZANDO"   => 'POWERBI AVANZADO',
        "SAP HCM EXCEL"        => 'SAP HCM',
        'POWER'                => 'POWERBI BASICO',
        'MS PROJ'              => 'MS PROJECT',
        'EXCEL POWERBI'        => 'EXCEL',
        'SAP PM EXCEL'         => 'SAP PM',
    ];

    /*
    array de nombres de curso mal escritos y colocado al correcto.
    colocar la KEY en MAYUSCULAS!!
    */
    private $__courseNameReplacements = [
        "PB"                   => "POWERBI BASICO",
        "POWER BI"             => "POWERBI BASICO",
        "MSPJ"                 => "MS PROJECT",
        "MS"                   => "MS PROJECT",
        "MSP"                  => "MS PROJECT",
        "POWERBI"              => "POWERBI BASICO",
        "MSPROJ"               => "MS PROJECT",
        "EXCL"                 => "EXCEL",
        "POWRB"                => "POWERBI BASICO",
        "FI"                   => "SAP FI",
        "MSPROJECT"            => "MS PROJECT",
        "SAP MM EXCEL"         => "SAP MM",
        "EXCELL"               => "EXCEL",
        "MS PRJ"               => "MS PROJECT",
        "POWER BI AVANZADO"    => "POWERBI AVANZADO",
        "SAP MM (PROMO) EXCEL" => "SAP MM",
        "EXCEL EMPRESARIAL"    => "EXCEL",
        "PP"                   => "SAP PP",
        "MS PROJEC"            => "MS PROJECT",
        "SAP  MM"              => "SAP MM",
        "POWER BI  AVANZADO"   => "POWERBI AVANZADO",
        "SAP ABAP"             => "SAP PP",
        "POWER BI AVANZANDO"   => "POWERBI AVANZADO",
        "SAP HCM EXCEL"        => "SAP HCM",
        "POWER"                => "POWERBI BASICO",
        "MS PROJ"              => "MS PROJECT",
        "EXCEL POWERBI"        => "EXCEL",
        "SAP PM EXCEL"         => "SAP PM",
        "MM" => "SAP MM",
        "PM" => "SAP PM",
        "HCM" => "SAP HCM",
        "INTEGRAL" => "SAP INTEGRAL",
    ];


    public function index($sheet_type = 'test')
    {
        // memory_limit
        ini_set('memory_limit', '2048M');
        // set_time_limit
        set_time_limit(0);

        self::createGoogleServiceInstance();
        $data = self::getSheetsData($sheet_type);
        // return $data;
        $data = self::formatCourses($data);
        $data = self::formatProgress($data);

        return $data;
    }

    public function arrayToCollectionRecursive($array)
    {
        return collect($array)->map(function ($item) {
            if (is_array($item)) {
                return self::arrayToCollectionRecursive($item);
            }
            return $item;
        });
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
        foreach ($data as $i => $student) {
            $student['CORREO'] = strtolower($student['CORREO']);
            $data[$i]['wp_user_id'] = isset($users_db[$student['CORREO']]) ? $users_db[$student['CORREO']] : null;

            if (!$data[$i]['wp_user_id']) {
                $col = '';
                if ($data[$i]['USUARIO AULA']) {
                    $col = 'USUARIO AULA';
                } elseif ($data[$i]['MSP USUARIO AULA']) {
                    $col = 'MSP USUARIO AULA';
                } elseif ($data[$i]['PBI USUARIO AULA']) {
                    $col = 'PBI USUARIO AULA';
                } elseif ($data[$i]['EXC USUARIO AULA']) {
                    $col = 'EXC USUARIO AULA';
                }

                if ($col) {
                    $data[$i]['wp_user_id'] = isset($users_db2[$student[$col]]) ? $users_db2[$student[$col]] : null;
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
            foreach ($courses_names as $course_name) {
                if (in_array($course_name, ['PP', 'MM', 'PM', 'HCM', 'FI', 'INTEGRAL', 'SAP PP', 'SAP MM', 'SAP PM', 'SAP HCM', 'SAP FI', 'SAP INTEGRAL'])) {
                    $sapNumber++;
                }
            }

            $enable = [];
            if ($sapNumber > 1) {
                $string = '';
                if (strpos($student['ESTADO'], 'HABILITADO ') !== false) {
                    $string = 'HABILITADO ';
                }
                if (strpos($student['ESTADO'], 'HABILITAR ') !== false) {
                    $string = 'HABILITAR ';
                }
                if (strpos($student['ESTADO'], 'AL DIA/ CUOTAS ') !== false) {
                    $string = 'AL DIA/ CUOTAS ';
                }
                if (strpos($student['ESTADO'], 'CONTADO ') !== false) {
                    $string = 'CONTADO ';
                }
                if (strpos($student['ESTADO'], 'DESCONGELADO ') !== false) {
                    $string = 'DESCONGELADO ';
                }
                if (strpos($student['ESTADO'], 'CONGELADO ') !== false) {
                    $string = 'CONGELADO ';
                }

                if ($string) {
                    $enable = explode($string, $student['ESTADO'])[1];
                    $enable = explode('/', $enable)[0];
                    $enable = explode(' PENDIENTE', $enable)[0];
                    $enable = explode(' ', $enable);
                    $enable = array_map(function ($item) {
                        return 'SAP ' . trim($item);
                    }, $enable);
                }
            }

            foreach ($courses_names as $course_name) {

                if (in_array($course_name, ['PP', 'MM', 'PM', 'HCM', 'FI', 'INTEGRAL'])) {
                    $course_name = 'SAP ' . $course_name;
                }

                if (!$course_db = DB::table('courses')->where('short_name', $course_name)->when(isset($this->badCourses[$course_name]), function ($q) use ($course_name) {
                    return $q->orWhere('short_name', $this->badCourses[$course_name]);
                })->first()) {

                    $courses_not_found[] = $course_name;
                    continue;
                }


                $order_id = null;
                if ($order = WpLearnpressUserItem::select('ref_id')->where('user_id', $data[$i]['wp_user_id'])->where('item_id', $course_db->wp_post_id)->first()) {
                    $order_id = $order->ref_id;
                }

                if (strpos($course_name, 'SAP') !== false) {

                    $c = [
                        'course_id'                  => $course_db->id,
                        'sap_user'                   => $student['USUARIO SAP'],
                        'name'                       => $course_db->name,
                        'access'                     => $student['ACCESOS'],
                        'course_status_original'     => $student['AULA SAP'],
                        'certifaction_test_original' => $student['EXAMEN'],
                        'start'                      => null,
                        'end'                        => null,
                        'order_id'                   => $order_id,
                        'certificate'                => $student['CERTIFICADO'],
                        'wp_post_id'                 => $course_db->wp_post_id,
                        'type'                       => 'paid'
                    ];

                    if (in_array($course_name, $enable) || $sapNumber == 1) {
                        $start = $student['INICIO'];
                        $end = $student['FIN'];
                        // Se comenta este codigo porque la fecha fiene en formato Y-m-dTH:i:s
                        // try {
                        //     $start = $start ?  Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d') : null;
                        //     $end = $end ?  Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d') : null;
                        // } catch (\Throwable $th) {
                        //     $start = null;
                        //     $end   = null;
                        // }

                        try {
                            // Valida o por "d/m/Y" con la separación por "/", o por "Y-m-d" con la separación por "-"
                            $start = self::__parseDate($start);
                            $end = self::__parseDate($end);
                        } catch (\Throwable $th) {
                            $start = null;
                            $end   = null;
                        }

                        $courses[] = $c;
                        $courses[count($courses) - 1]['start'] = $start;
                        $courses[count($courses) - 1]['end'] = $end;
                    }

                    if (!in_array($course_name, $enable) && $sapNumber > 1) {
                        $inactive_courses[] = $c;
                    }
                }

                // If not include SAP courses
                $colsAccesos      = [6 => "EXC ACCESOS", 7 => "PBI ACCESOS", 8 => "PBI ACCESOS", 9 => "MSP ACCESOS"];
                $colsCourseStatus = [6 => "EXCEL", 7 => "PBI", 8 => "PBI", 9 => "MS PROJECT"];

                $colsCertificationStatus = [7 => "PBI EXAMEN", 8 => "PBI", 9 => "MSP EXAMEN"];

                if (strpos($course_name, 'SAP') === false) {
                    $dates = [
                        // Excel
                        6 => ['start' => 'EXC INICIO', 'end' => 'EXC FIN', 'certificate' => 'EXC CERTIF. AVA'],
                        // Fundamentos de Power BI
                        7 => ['start' => 'PBI INICIO', 'end' => 'PBI FIN', 'certificate' => 'PBI CERTIFICADO'],
                        // Power BI para el Análisis de Datos
                        8 => ['start' => 'PBI INICIO', 'end' => 'PBI FIN', 'certificate' => 'PBI CERTIFICADO'],
                        // Fundamentos de MS Project 2019
                        9 => ['start' => 'MSP INICIO', 'end' => 'MSP FIN', 'certificate' => 'MSP CERTIFICADO'],
                    ];


                    $start = $student[$dates[$course_db->id]['start']];
                    $end = $student[$dates[$course_db->id]['end']];
                    try {
                        $start = self::__parseDate($start);
                        $end = self::__parseDate($end);
                    } catch (\Throwable $th) {
                        $start = null;
                        $end   = null;
                    }



                    $courses[] = [
                        'course_id'              => $course_db->id,
                        'sap_user'               => $student['USUARIO SAP'],
                        'name'                   => $course_db->name,
                        'access'                 => $student[$colsAccesos[$course_db->id]],
                        'course_status_original' => $student[$colsCourseStatus[$course_db->id]],
                        'start'                  => $start,
                        'end'                    => $end,
                        'order_id'               => $order_id,
                        'certificate'            => $student[$dates[$course_db->id]['certificate']],
                        'wp_post_id'             => $course_db->wp_post_id,
                        'type'                   => 'free'
                    ];

                    if ($course_db->id != 6) {
                        $courses[count($courses) - 1]['certifaction_test_original'] = $student[$colsCertificationStatus[$course_db->id]];
                    }
                }
            }

            $data[$i]['courses'] = $courses;
            $data[$i]['inactive_courses'] = $inactive_courses;
        }

        // $courses_not_found =  array_values(array_unique($courses_not_found));

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

                    // $data[$i]['courses'][$j]['______lessons_count'] = $lessons[$course['wp_post_id']];
                    // $data[$i]['courses'][$j]['______lessons_completed'] = $lessons_completed->map(function($q){
                    //     return $q->item_id;
                    // })->toArray();

                    // Get diff between lessons and lessons completed
                    // $data[$i]['courses'][$j]['______lessons_diff'] = array_diff($lessons[$course['wp_post_id']], $lessons_completed->map(function($q){
                    //     return $q->item_id;
                    // })->toArray());







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

    public function getSheetsData($sheet_type = 'test')
    {
        $sheets = Sheet::where('type', $sheet_type)->get();


        $data = [];
        foreach ($sheets as $sheet) {
            $ranges = ['BASE!A1:ZZZ1000', 'CURSOS!A1:ZZZ1000'];

            $response = $this->service->spreadsheets_values->batchGet($sheet->sheet_id, ['ranges' => $ranges]);

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
                            $mergedRow['sheet_id']          = $sheet->sheet_id;
                            $mergedRow['course_tab_id']     = $sheet->course_tab_id;
                            $mergedRow['base_tab_id']       = $sheet->base_tab_id;
                            $data[]                         = $mergedRow;
                        }
                    }
                }
            }
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

        $this->service = new Google_Service_Sheets($client);
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

    /**
     * Formatea el nombre de un curso "PP" => "SAP PP"
     * @param string $name Nombre del curso
     */
    private function __parseCourseNameReplacements($name)
    {
        $name = strtoupper($name);
        $name = trim($name);
        if (array_key_exists($name, $this->__courseNameReplacements) == true) :
            return $this->__courseNameReplacements[$name];
        else :
            return $name;
        endif;
    }

    /**
     * Parsea las observaciones de un estudiante que generalmente vienen en $student['OBSERVACIONES']
     * @param string $observations Observaciones del estudiante
     * @return array observaciones encontradas
     */
    public function parseObservations($observations)
    {
        $observationsArray = [];

        // Verificamos si la cadena no está vacía
        if (!empty($observations)) {
            // Dividimos las observaciones utilizando el separador "/"
            $observationList = explode('/', $observations);
            foreach ($observationList as $observation) :
                if (preg_match('/^\s*(REPROBADO|ABANDONADO|CERTIFICADO|NO CULMINÓ|APROBADO)\s+(.+)\s*$/', $observation, $matches)) :
                    // $matches[1] contendrá el estado y $matches[2] contendrá el nombre del curso
                    $estado = $matches[1];
                    $nombreCurso = $this->__parseCourseNameReplacements($matches[2]);

                    $observationsArray[$nombreCurso] = $estado;
                endif;
            endforeach;
        }

        return $observationsArray;
    }

    /**
     * Formatea el campo "OBSERVACIONES" de un estudiante
     */
    public function formatProgressObservations(&$student)
    {
        if (isset($student['OBSERVACIONES']) == false) :
            return;
        endif;

        // Verificamos si existe el campo "OBSERVACIONES" en $student
        // Utilizamos la función parseObservations para obtener el array de observaciones
        $observationsArray = $this->parseObservations($student['OBSERVACIONES']);
        foreach ($observationsArray as $courseName => $courseStatus) {
            $courseLongName = DB::table('courses')->where('short_name', $courseName)->value('name');
            if (empty($courseLongName) == true) :
                continue;
            endif;

            // Recorremos los cursos del estudiante
            foreach ($student['courses'] as &$course) {
                // Verificamos si el nombre del curso existe en las observaciones
                if ($course['name'] == $courseLongName) {
                    // Actualizamos el estado del curso con el estado de las observaciones
                    $course['course_status'] = $courseStatus;
                    $course['course_status_original'] = $courseStatus;
                }
            }
            // Recorremos los cursos INACTIVOS del estudiante
            $newInactiveCourses = []; // para eliminar los cursos inactivos
            for ($i = 0; $i < count($student['inactive_courses']); $i++) :
                $course2 = $student['inactive_courses'][$i];
                // Verificamos si el nombre del curso existe en las observaciones
                if ($course2['name'] == $courseLongName) :
                    // Actualizamos el estado del curso con el estado de las observaciones
                    $course2['course_status'] = $courseStatus;
                    // $course['course_status_original'] = $courseStatus;
                    $student['courses'][] = $course2; // lo agrega al array de cursos
                else :
                    // este curso aun es inactivo
                    $newInactiveCourses[] = $course2;
                endif;
            endfor;
            $student['inactive_courses'] = $newInactiveCourses;
        }
    }

    /**
     * Parsea el campo "ESTADO" de un estudiante que generalmente vienen en $student['ESTADO']
     * Se asume que el formato es "HABILITADO HCM FI / PENDIENTE MM" es decir "ESTADO CURSO1 CURSO2 / ESTADO CURSO3"
     * @param string $studentState Observaciones del estudiante
     * @return array observaciones encontradas
     */
    public function parseStudentState($studentState)
    {
        $observationsArray = [];

        // Verificamos si la cadena no está vacía
        if (!empty($studentState)) {
            // Dividimos las observaciones utilizando el separador "/"
            $observationList = explode('/', $studentState);
            foreach ($observationList as $observation) :
                // @TODO: Revisar si es necesario agregar más estados como "AL DIA CUOTAS".
                if (preg_match('/^\s*(HABILITADO|HABILITAR|CONTADO|DESCONGELADO|CONGELADO|PENDIENTE)\s+(.+)\s*$/', $observation, $matches)) :
                    // $matches[1] contendrá el estado y $matches[2] contendrá el nombre del curso
                    $estado = $matches[1];
                    if (empty($matches[2]) == false) :
                        // 1 o varios cursos?
                        $coursesShortNames = explode(' ', $matches[2]);
                        foreach ($coursesShortNames as $courseShortName) :
                            $courseName = $this->__parseCourseNameReplacements($courseShortName);
                            if (empty($courseName) == false) :
                                $observationsArray[$courseName] = $estado;
                            endif;
                        endforeach;
                    endif;
                endif;
            endforeach;
        }

        return $observationsArray;
    }

    /**
     * Parsea el campo "AULA SAP" de un estudiante que generalmente vienen en $student['AULA SAP']
     * Se asume que el formato es "HABILITADO HCM FI / PENDIENTE MM" es decir "ESTADO CURSO1 CURSO2 / ESTADO CURSO3"
     * @param string $state Observaciones del estudiante
     * @return array observaciones encontradas
     */
    public function parseAulaSAP($state)
    {
        $observationsArray = [];

        // Verificamos si la cadena no está vacía
        if (!empty($state)) {
            // Dividimos las observaciones utilizando el separador "/"
            $observationList = explode('/', $state);
            foreach ($observationList as $observation) :
                // @TODO: Revisar si es necesario agregar más estados como "AL DIA CUOTAS".
                if (preg_match('/^\s*(CURSANDO|COMPLETA)\s+(.+)\s*$/', $observation, $matches)) :
                    // $matches[1] contendrá el estado y $matches[2] contendrá el nombre del curso
                    $estado = $matches[1];
                    if (empty($matches[2]) == false) :
                        // 1 o varios cursos?
                        $coursesShortNames = explode(' ', $matches[2]);
                        foreach ($coursesShortNames as $courseShortName) :
                            $courseName = $this->__parseCourseNameReplacements($courseShortName);
                            if (empty($courseName) == false) :
                                $observationsArray[$courseName] = $estado;
                            endif;
                        endforeach;
                    endif;
                endif;
            endforeach;
        }

        return $observationsArray;
    }


    /**
     * Ajustes varios a los cursos 
     * Pablo
     */
    public function fixCourses(&$student)
    {

        // El siguiente FIX es para el campo AULA SAP
        $studentAulaSAP = $this->parseAulaSAP($student['AULA SAP']);

        foreach ($studentAulaSAP as $courseName => $courseStatus) :

            $courseLongName = DB::table('courses')->where('short_name', $courseName)->value('name');
            if (empty($courseLongName) == true) :
                continue;
            endif;
            // itera por cada curso
            for ($i = 0; $i < count($student['courses']); $i++) :
                if ($student['courses'][$i]['name'] == $courseLongName) :
                    $student['courses'][$i]["course_status"] = $courseStatus;
                endif;
            endfor;
        endforeach;

        // El siguiente FIX es para 1 curso SAP que tiene 
        // - Aula SAP: COMPLETA
        //- Examen: Aprobado
        //- Certificado: Emitido
        for ($i = 0; $i < count($student['courses']); $i++) :
            if (stripos($student['courses'][$i]['name'], "SAP ") !== false // si es SAP
            && ($student['courses'][$i]["course_status_original"] == "COMPLETA")
            && ($student['courses'][$i]["certifaction_test_original"] == "Aprobado")
            && ($student['courses'][$i]["certificate"] == "EMITIDO")
            ) :
                $student['courses'][$i]["certifaction_test_original"] = "CERTIFICADO";
                $student['courses'][$i]["course_status"] = "CERTIFICADO";
            endif;
        endfor;


        // El siguiente FIX normaliza los estados de cursos en base a su certifaction_test_original y course_status_original
        $excelLevels = ["nivel_basico", "nivel_intermedio", "nivel_avanzado"];
        for ($i = 0; $i < count($student['courses']); $i++) :
            // Si el curso es SAP o de obsequio
            if (
                isset($student['courses'][$i]['certifaction_test_original']) == true
                && isset($student['courses'][$i]['course_status_original']) == true
                && isset($student['courses'][$i]['course_status']) == true
            ) :
                $student['courses'][$i]['course_status'] = $this->__recalculateCourseStatus($student['courses'][$i]['certifaction_test_original'], $student['courses'][$i]['course_status_original'], $student['courses'][$i]['course_status']);
            elseif (
                isset($student['courses'][$i]['course_status_original']) == true
                && isset($student['courses'][$i]['course_status']) == true
            ) :
                $student['courses'][$i]['course_status'] = $this->__recalculateCourseStatus("", $student['courses'][$i]['course_status_original'], $student['courses'][$i]['course_status']);
            endif;

            foreach ($excelLevels as $level) :
                if (
                    isset($student['courses'][$i][$level]) == true
                    && isset($student['courses'][$i][$level]['certifaction_test_original']) == true
                    && isset($student['courses'][$i][$level]['course_status']) == true

                ) :
                    $student['courses'][$i][$level]['course_status'] = $this->__recalculateCourseStatus($student['courses'][$i][$level]['certifaction_test_original'], $student['courses'][$i][$level]['course_status'], $student['courses'][$i][$level]['course_status']);
                endif;
            endforeach;

        endfor;

        // El siguiente FIX procesará el ESTADO de cursos para detectar los PENDIENTE que se encuentran en el campo de cursos inactivos
        //********************************************************* */
        // CAMBIOS PABLO
        $studentStates = $this->parseStudentState($student['ESTADO']);

        foreach ($studentStates as $courseName => $courseStatus) :

            $courseLongName = DB::table('courses')->where('short_name', $courseName)->value('name');
            if (empty($courseLongName) == true) :
                continue;
            endif;

            $newInactiveCourses = []; // para eliminar los cursos inactivos
            for ($i = 0; $i < count($student['inactive_courses']); $i++) :
                $course = $student['inactive_courses'][$i];
                if ($course['name'] == $courseLongName) :
                    if ($courseStatus == 'PENDIENTE') :
                        $course['course_status'] = 'POR HABILITAR';
                        $course['course_status_original'] = $courseStatus; // deja "PENDIENTE"
                        $student['courses'][] = $course; // lo agrega al array de cursos
                    else :
                        // este curso inactivo aun es valido
                        $newInactiveCourses[] = $course;
                    endif;
                endif;
            endfor;
            $student['inactive_courses'] = $newInactiveCourses;
        endforeach;

        // el siguiente FIX es para el campo OBSERVACIONES
        $this->formatProgressObservations($student);
    }


    /**
     * Recalcula el estado de un curso basado en la certificacion y el estado del curso original de Excel
     * IMportante: la priorización es CERTIFICADO primero y luego el ESTADO del curso
     * @param string $certification_test_original Estado de la certificación original de Excel
     * @param string $course_status_original Estado ORIGINAL del curso en el Excel
     * @param string $courseStatus Estado ACTUAL del curso
     * @return string Estado del curso recalculado
     */
    private function __recalculateCourseStatus($certification_test_original, $course_status_original, $courseStatus)
    {

        $aCertificationToCourseStatusMap = [
            "CERTIFICADO" => "CERTIFICADO",
            "APROBADO" => "APROBADO",
            "REPROBADO" => "REPROBADO",
            // los estados de abajo no aplican al estado del curso
            // "NO APLICA" => "NO APLICA",
            // "1 INTENTO PENDIENTE" => "1 INTENTO PENDIENTE",
            // "2 INTENTOS PENDIENTES" => "2 INTENTOS PENDIENTES",
            // "3 INTENTOS PENDIENTES" => "3 INTENTOS PENDIENTES",
            // "INTENTOS PENDIENTES" => "INTENTOS PENDIENTES",
            // "SIN INTENTOS GRATIS" => "SIN INTENTOS GRATIS",

        ];

        $aCourseStatusOriginalToCourseStatuMap = [
            "ABANDONADO" => "ABANDONADO",
            "ABANDONO" => "ABANDONADO",
            "ABANDONÓ" => "ABANDONADO",
            "APROBADO" => "APROBADO",
            "APROBO" => "APROBADO",
            "CERTIFICADO" => "CERTIFICADO",
            "COMPLETA" => "COMPLETA",
            "COMPLETA SIN CREDLY" => "COMPLETA",
            "CONGELADO" => "CONGELADO",
            "CONTADO" => "CURSANDO",
            "CURSANDO" => "CURSANDO",
            "CURSANDO AVANZADO" => "CURSANDO",
            "CURSANDO AVANZADO SIN CREDLY" => "CURSANDO",
            "CURSANDO SIN CREDLY" => "CURSANDO",
            "DESAPROBO" => "REPROBADO",
            "DESCONGELADO" => "DESCONGELADO",
            "HABILITADO" => "CURSANDO",
            "HABILITAR" => "POR HABILITAR",
            "NO APLICA" => "NO APLICA",
            "no aprobo" => "REPROBADO",
            "NO CULMINO" => "NO CULMINÓ",
            "NO CULMINÓ" => "NO CULMINÓ",
            "PENDIENTE" => "POR HABILITAR",
            "POR HABILITAR" => "POR HABILITAR",
            "POR HABILITAR AVANZADO" => "POR HABILITAR",
            "REPROBADO" => "REPROBADO",
        ];

        $certification_test_original =  trim(strtoupper($certification_test_original));
        $course_status_original =  trim(strtoupper($course_status_original));

        // Si hay un mapeo en el CERTIFICADO, se usa ese estado como nuevo estado del curso
        if (key_exists($certification_test_original, $aCertificationToCourseStatusMap)) {
            return $aCertificationToCourseStatusMap[$certification_test_original];
        }

        // Si hay un mapeo en el ESTADO ORIGINAL, se usa ese estado como nuevo estado del curso
        if (key_exists($course_status_original, $aCourseStatusOriginalToCourseStatuMap)) {
            return $aCourseStatusOriginalToCourseStatuMap[$course_status_original];
        }

        // Si no, se usa el estado actual del curso
        return $courseStatus;
    }

    public function testParseState()
    {
        $tests = [
            "HABILITADO HCM",
            "HABILITADO HCM",
            "HABILITADO HCM FI",
            "PENDIENTE HCM FI",
            "HABILITADO HCM FI / PENDIENTE MM",
        ];

        foreach ($tests as $test) {
            $result = $this->parseStudentState($test);
            printf("<pre>'%s' \n=> %s</pre>", $test, print_r($result, true));
            dump($result);
        }
    }

    public function testAulaSap()
    {
        $tests = [
            "CURSANDO",
            "CURSANDO HCM",
            "CURSANDO HCM FI",
            "COMPLETA HCM FI",
            "CURSANDO HCM FI / COMPLETA MM",
        ];

        foreach ($tests as $test) {
            $result = $this->parseAulaSAP($test);
            printf("<pre>'%s' \n=> %s</pre>", $test, print_r($result, true));
            dump($result);
        }
    }
}
