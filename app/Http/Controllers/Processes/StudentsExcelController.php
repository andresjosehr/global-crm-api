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
            // var_dump($student[0]);
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
                if($order = WpLearnpressUserItem::select('ref_id')->where('user_id', $data[$i]['wp_user_id'])->where('item_id', $course_db->wp_post_id)->first()){
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
                            $start = $start ?  Carbon::parse($start)->format('Y-m-d') : null;
                            $end = $end ?  Carbon::parse($end)->format('Y-m-d') : null;
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
                        $start = $start ?  Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d') : null;
                        $end = $end ?  Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d') : null;
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

                    if($course_db->id != 6){
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
        $lessons = DB::connection('wordpress')->table('globalposts as lessons')
            ->select('lessons.*', 'sections.section_course_id', 'sections.section_name')
            ->join('globallearnpress_section_items as section_items', 'section_items.item_id', '=', 'lessons.ID')
            ->join('globallearnpress_sections as sections', 'sections.section_id', '=', 'section_items.section_id')
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
        $quizzes = DB::connection('wordpress')->table('globalposts as quizzes')
            ->select('quizzes.ID', 'sections.section_course_id')
            ->join('globallearnpress_section_items as section_items', 'section_items.item_id', '=', 'quizzes.ID')
            ->join('globallearnpress_sections as sections', 'sections.section_id', '=', 'section_items.section_id')
            ->join('globalpostmeta as pm', function ($join) {
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
}
