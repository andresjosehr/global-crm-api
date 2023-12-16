<?php

namespace App\Console\Commands;

use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeCoursesCompletedTextOneMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-complete-free-courses-onemonth';

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
    public function handle($students = null)
    {
        if (!$students) {
            // $data = new StudentsExcelController();
            //$students = $data->index('test');
            $students = json_decode(file_get_contents(storage_path('app/public/data.json')), true);
        }

        // Get data from storage/app/public/data.json



        return $this->line(json_encode($students));
        // Filtrar estudiantes que tienen cursos de tipo "free", course_status_original COMPLETA  o COMPLETA SIN CREDLY y
        //certifaction_test_original 'Sin Intentos Gratis'
        $students = array_filter($students, function ($student) {
            $freeAndCompletedCourses = self::filtreFeeAndCompleteCourses($student);
            return !empty($freeAndCompletedCourses);
        });

        $students = array_values($students);


        // transformar student para agregarle diff_days en course
        $students = array_map(function ($student) {
            $student['NOMBRE'] = (isset($student['NOMBRE'])) ? $student['NOMBRE'] : $student['NOMBRES Y APELLIDOS'];
            $text = '';

            $courses = array_map(function ($course) use ($student) {
                $now   = Carbon::now();
                $end = Carbon::parse($course['end']);
                $month_days = $end->daysInMonth;
                $diff = $end->diffInDays($now) + 1;
                $course['diff_days'] = $diff;
                $course['month_days'] = $month_days;

                return $course;
            }, $student['courses']);
            $student['courses'] = $courses;
            $text = self::generateOneMonthDiffText($student);

            $student['text'] = view('especial-messages.complete-free-courses.1-mes', ['student' => $student, 'text' => $text])->render();
            $student['text'] = preg_replace("/^\s+/m", "", $student['text']);
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            $student['text'] = str_replace('breakline', "\n", $student['text']);

            return $student;
        }, $students);
        dd($students);
        return $this->line(json_encode($students));
    }

    public static function filtreFeeAndCompleteCourses($student)
    {
        $freeAndCompletedCourses = array_filter($student['courses'], function ($course) {
            $isExcelEmpresarial = $course['name'] == 'Excel Empresarial';

            if ($isExcelEmpresarial) {
                return (
                    $course['type'] == 'free' &&
                    in_array($course['course_status_original'], ['COMPLETA', 'COMPLETA SIN CREDLY']) &&
                    $course['end'] != null &&
                    (
                        $course['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis' ||
                        $course['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis' ||
                        $course['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis'
                    )
                );
            }
            // No es 'Excel Empresarial', aplicar otras condiciones si es necesario
            return (
                $course['type'] == 'free' &&
                in_array($course['course_status_original'], ['COMPLETA', 'COMPLETA SIN CREDLY']) &&
                isset($course['certifaction_test_original']) &&
                $course['certifaction_test_original'] == 'Sin Intentos Gratis' &&
                $course['end'] != null
            );
        });

        return $freeAndCompletedCourses;
    }

    public static function generateOneMonthDiffText($student)
    {
        $text = '';
        $s = count($student['courses']) > 1 ? 's' : '';
        $one_day = []; // un dia
        $four_days = []; // cuatro dias
        $seven_days = []; // siete dias
        $fifteen_days = []; // 15 dias
        $one_month = []; // numero de mes al terminar, ejemplo si en septiembre termina tu curso va a tener el numero de dias de septiembre
        $excel = []; // verifica si excel
        $mbi_and_msp_siF = 0; // CANTIDAD DE CURSOS SIN INTENTOS GRATIS
        $excel_siF = 0;
        $couse_name_mpb_and_pbi = []; // CURSOS OBSEQUIO CON INTENTO GRATIS
        $excel_level_siF = []; // EXCEL CON INTENTOS GRATIS
        $excel_level_apro = 0; // excel de nivel aprobado
        $salto = "\n";

        foreach ($student['courses'] as $key => $course) {
            switch ($course['diff_days']) {
                case 1:
                    $one_day[] = $course;
                    break;
                case 4:
                    $four_days[] = $course;
                    break;
                case 7:
                    $seven_days[] = $course;
                    break;
                case 15:
                    $fifteen_days[] = $course;
                    break;
                default: {
                        if ($course['diff_days'] == $course['month_days'])
                            $one_month[] = $course;
                        break;
                    }
            }
        }

        foreach ($one_month as $key => $course) {
            if (isset($course['certifaction_test_original'])) {
                if ($course['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $mbi_and_msp_siF++;
                    $couse_name_mpb_and_pbi[] = $course;
                }
            }
            $ExcelEmpresarial = $course['name'] == 'Excel Empresarial';
            if ($ExcelEmpresarial) {
                $excel =  $course;
                if ($excel['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $excel_siF++;
                    $excel_level_siF[] =  ['name' => 'Basico'];
                } elseif ($excel['nivel_basico']['certifaction_test_original'] == 'Aprobado') {
                    $excel_level_apro++;
                }

                if ($excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $excel_siF++;
                    $excel_level_siF[] = ['name' => 'Intermedio'];
                } elseif ($excel['nivel_intermedio']['certifaction_test_original'] == 'Aprobado') {
                    $excel_level_apro++;
                }

                if ($excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $excel_siF++;
                    $excel_level_siF[] =  ['name' => 'Avanzado'];
                } elseif ($excel['nivel_avanzado']['certifaction_test_original'] == 'Aprobado') {
                    $excel_level_apro++;
                }
            }
        }

        if (sizeof($one_month) > 0) {
            if (sizeof($one_month) == 1)
                $text .= 'Está por vencer tu curso: ' . "\n";
            else
                $text .= 'Están por vencer tus cursos: ' . "\n";

            $text = self::setCoursesName($one_month);
            if ($mbi_and_msp_siF == 1) {
                $text .= ' 🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.' . "\n";
            } elseif ($mbi_and_msp_siF == 2) {
                $text .= '🚨 Actualmente estos cursos se encuentran reprobados y no brindamos certificados por participación.' . "\n";
            }

            if ($mbi_and_msp_siF == 2 && (
                $excel['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis'
                || $excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis'
                || $excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis')) {
                $text .= "🚨 Actualmente estos cursos *se encuentran reprobados y para Excel, debes aprobar los 3 niveles,*
            porque no brindamos certificados por participación, ni por nivel independiente, y este es el estado de cada nivel del curso:" . "\n";
                $text .= "- NIVEL BASICO ESTADO: " . "\n";
                $text .= $excel['nivel_basico']['certifaction_test_original'] . "\n";
                $text .= "- NIVEL INTERMEDIO ESTADO:" . "\n";
                $text .= $excel['nivel_intermedio']['certifaction_test_original'] . "\n";
                $text .= "- NIVEL AVANZADO ESTADO: " . "\n";
                $text .= $excel['nivel_avanzado']['certifaction_test_original'] . "\n";
            }

            if ($mbi_and_msp_siF == 0 && (
                $excel['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis'
                || $excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis'
                || $excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis')) {
                $text .= "🚨 A continuación te indico el estado actual de cada nivel:" . "\n";
                $text .= "- NIVEL BASICO ESTADO: " . "\n";
                $text .= $excel['nivel_basico']['certifaction_test_original'] . "\n";
                $text .= "- NIVEL INTERMEDIO ESTADO:" . "\n";
                $text .= $excel['nivel_intermedio']['certifaction_test_original'] . "\n";
                $text .= "- NIVEL AVANZADO ESTADO: " . "\n";
                $text .= $excel['nivel_avanzado']['certifaction_test_original'] . "\n";
            }
            // linea 42 y 43
            $text .= '🚩 🚩 *Pero no todo está perdido.*' . "\n";
            $text .= '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*' . "\n";

            // linea 44
            if ($mbi_and_msp_siF > 1 || sizeof($excel_level_siF) > 1)
                $text .= 'CURSOS y NIVELES DE EXCEL SIN INTENTOS GRATIS' . "\n";
            else
                $text .= 'CURSO y NIVEL DE EXCEL SIN INTENTOS GRATIS' . "\n";

            foreach ($couse_name_mpb_and_pbi as $mpb_pbi) {
                $text .= '-' . $mpb_pbi['name']  . "\n";
            }

            foreach ($excel_level_siF as $level) {
                $text .= '-Nivel ' . $level['name'] . "\n";
            }
            // LINEA 45
            if ($excel_siF > 1)
                $text .= 'NIVELES DE EXCEL SIN INTENTOS GRATIS' . "\n";
            else
                $text .= 'NIVEL DE EXCEL SIN INTENTOS GRATIS' . "\n";
            foreach ($excel_level_siF as $level) {
                $text .= '-Nivel ' . $level['name'] . "\n";
            }


            // LINEA 46
            if (($student['AULA SAP'] == 'CURSANDO') && ($mbi_and_msp_siF > 0 || sizeof($excel_level_siF) > 0)) {
                $text .= 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.' . "\n";
            }


            // LINEA 47

            if (($student['EXAMEN'] == 'aprobado') || (($student['PONDERADO SAP'] == 'pagado') && ($mbi_and_msp_siF > 0 || sizeof($excel_level_siF) > 0))) {
                $text .= 'Y de esta manera obtener tus certificados.' . "\n";
            }

            //falta la linea 48

            // linea 49, los cursos pbi o msp estan en $mbi_and_msp_siF si es 1 puede ser mbp/ms  por lo tanto cualquier de los dos
            if ($student['EXAMEN'] == 'aprobado' || ($student['PONDERADO SAP'] == 'pagado' && $mbi_and_msp_siF >= 1)) {
                $text .= 'Y de esta manera obtener tu certificado.' . "\n";
            }


            //linea 50
            if ($excel_level_apro == 1) {
                $text .= '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobado un nivel, no pierdas la oportunidad.' . "\n";
            }

            //linea 51
            if ($excel_level_apro == 2) {
                $text .= '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobados dos niveles, no pierdas la oportunidad.' . "\n";
            }
            $has_course_cursando = self::hasCourseCursando($student['courses']);
            // 54 a 58
            if ($has_course_cursando) {
                $text .= '👀 OJO aún estás cursando:' . "\n";
                foreach ($student['courses'] as $key => $course) {
                    if (isset($course['certifaction_test_original'])) {
                        /// arreglar este texto, tiene que aparecer al principio de todas esas condicions
                        if ($course['course_status_original'] == 'CURSANDO' && $course['month_days'] != $course['diff_days']) {
                            // $text .= 'CURSO' . "\n";
                            $text .= $course['name'] . "\n";
                        }

                        // linea 58, nombre de los cursos obsequio que terminan en un mes
                        if ($course['month_days'] == $course['diff_days']) {
                            $text .= $course['name'] . "\n";
                        }
                    }
                }
                //linea 56
                if (
                    ($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') &&
                    ($student['CERTIFICADO'] != 'EMITIDO') &&
                    ($course['course_status_original'] == 'CURSANDO')
                ) {
                    $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados*,
                        y aún no te certificas en SAP. Por lo que podrías perder el acceso, a pesar de haber iniciado, si no pagas el ponderado de: ' . "\n";
                }
                //linea 57

                if (($student['EXAMEN'] == 'reprobado' || $student['EXAMEN'] == 'sin intentos gratis') &&
                    ($course['course_status_original'] == 'CURSANDO')
                ) {
                    $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP.
                     Por lo que está en peligro el acceso, si no pagas el ponderado de: ' . "\n";
                }
            }

            // LINEA 61 85
            $status_por_habilitar = 0;
            $status_certifi_aprobado = 0;
            $cant_courses_reprobado = 0;
            $status_por_cursando = 0;
            $course_por_habilitar = [];
            $course_por_cursando = [];
            $course_por_aprobado = [];
            $course_reprobado = self::courseReprobado($student['courses']);
            $student['EXAMEN'] = strtolower($student['EXAMEN']);
            $student['PONDERADO SAP'] = strtolower($student['PONDERADO SAP']);
            $student['AULA SAP'] = strtolower($student['AULA SAP']);
            $student_condition = ($student['AULA SAP'] == 'cursando' || $student['AULA SAP'] == 'completa') && ($student['CERTIFICADO'] != 'EMITIDO');
            $course_sin_intentos_o_reprobado = [];
            $codicion_63 = [];
            $codicion_69 = [];
            $codicion_71 = [];


            foreach ($course_reprobado as $key => $course) {

                if (isset($course['certifaction_test_original'])) {
                    $course['certifaction_test_original'] = strtolower($course['certifaction_test_original']);
                    //LINEA 62
                    if (($course['certifaction_test_original'] == 'sin intentos pendientes' || $course['certifaction_test_original'] == 'reprobado') &&
                        ($course['month_days'] != $course['diff_days']) ||
                        (($course['start'] == null) && ($course['end'] == null))
                    ) {
                        $course_sin_intentos_o_reprobado[] = $course;
                    }

                    if (($student_condition) &&  ($course['course_status_original'] == 'COMPLETA' &&
                            ($course['certifaction_test_original'] == 'sin intentos pendientes' || $course['certifaction_test_original'] == 'reprobado')) &&
                        ($course['month_days'] != $course['diff_days']) ||
                        (($course['start'] == null) && ($course['end'] == null))
                    ) {

                        $codicion_63[] = $course;
                    }
                    //linea 66
                    $has_status_por_habilitar = self::getCourseByStatus($course, 'POR HABILITAR');
                    if ($has_status_por_habilitar) {
                        $status_por_habilitar++;
                        $course_por_habilitar[] = $course;
                    }

                    // linea 68
                    $has_status_cursando = self::getCourseByStatus($course, 'CURSANDO');
                    if ($has_status_cursando) {
                        $status_por_cursando++;
                        $course_por_cursando[] = $course;
                    }

                    // linea 70
                    $status_obsequi_aprobado = self::getCourseByCertificate($course, 'aprobado');
                    if ($status_obsequi_aprobado) {
                        $status_certifi_aprobado++;
                        $course_por_aprobado[] = $course;
                    }


                    // linea 73
                    $status_obsequi_reprobado = self::getCourseByCertificate($course, 'reprobado');
                    if ($status_obsequi_reprobado)
                        $cant_courses_reprobado++;

                    //linea 69
                    if (
                        $course['course_status_original'] == 'CURSANDO' &&
                        ($course['month_days'] != $course['diff_days'])
                    ) {
                        $codicion_69[] = $course;
                    }

                    //Linea 71
                    if (
                        $course['certifaction_test_original'] == 'aprobado' &&
                        (($course['month_days'] != $course['diff_days']) || ($course['start'] == null) && ($course['end'] == null))
                    ) {
                        $codicion_71[] = $course;
                    }
                }
            }
            //linea 63 continuacion de la condicion de excel
            $has_excelreprobado = self::excelreprobado($excel);
            if ($student_condition && $has_excelreprobado) {
                $codicion_63[] = $excel;
                $cant_courses_reprobado++;
            }

            //linea 61 COMIENZO
            if (sizeof($course_reprobado) > 0)
                $text .= '👀 *OJO completaste, pero reprobaste:* ' . "\n";
            //linea 62
            $text .= self::setCoursesName($course_sin_intentos_o_reprobado);
            //linea 63 y 64
            if (sizeof($codicion_63) > 0) {
                $text .= self::setCourses($codicion_63, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            //linea 66 67
            if (sizeof($course_por_habilitar) > 0) {
                $text .= self::setCourses($course_por_habilitar, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //linea 68 y 69
            if (sizeof($course_por_cursando) > 0) {
                $text .= 'A pesar de quedar pendiente, no podrás habilitar:' . $salto;
                if ($codicion_69) {
                    $text .= self::setCoursesName($codicion_69);
                }
            }
            //linea 70 y 71
            if (sizeof($course_por_aprobado) > 0) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_69) {
                    $text .= self::setCoursesName($codicion_69);
                }
            }

            // linea 73
            if ($student['AULA SAP'] == 'cursando') {
                $text .= 'Ya que tendrías (' . $cant_courses_reprobado . ') cursos reprobados/abandonados, así que
                *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            // linea 75 Y 76
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y
                no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:');
            }

            //linea 78  79
            if (sizeof($course_por_habilitar) > 0) {
                $text .= self::setCourses($course_por_habilitar, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //linea  80 y 81
            if (sizeof($course_por_cursando) > 0) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_69) {
                    $text .= self::setCoursesName($codicion_69);
                }
            }
            // linea 82 y 83
            if (sizeof($course_por_aprobado) > 0) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_71) {
                    $text .= self::setCoursesName($codicion_71);
                }
            }
            //linea 85
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= 'Ya que tendrías (' . $cant_courses_reprobado . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }


            $course_no_culminado = self::hasCoursenoCulminado($student['courses']);
            $status_por_habilitarculminado = 0;
            $status_por_cursandoculminado = 0;
            $status_certifi_aprobadoculminado = 0;
            $cant_coursesreprobado = 0;
            $course_por_aprobadoculminado = [];
            $course_por_habilitarculminado = [];
            $course_por_cursandoculminado = [];
            $codicion_96 = [];
            $codicion_98 = [];
            foreach ($course_no_culminado as $key => $course) {
                if (isset($course['certifaction_test_original'])) {

                    $has_status_habilitarculminado = self::getCourseByStatus($course, 'POR HABILITAR');
                    if ($has_status_habilitarculminado) {
                        $status_por_habilitarculminado++;
                        $course_por_habilitarculminado[] = $course;
                    }

                    $has_status_cursandoculminado = self::getCourseByStatus($course, 'CURSANDO');
                    if ($has_status_cursandoculminado) {
                        $status_por_cursandoculminado++;
                        $course_por_cursandoculminado[] = $course;
                    }

                    //linea 96 condicion
                    if (($course['course_status_original'] == 'CURSANDO') &&
                        ($course['month_days'] != $course['diff_days'])
                    ) {
                        $codicion_96[] = $course;
                    }

                    // linea 97
                    $status_obsequi_aprobadoculminado = self::getCourseByCertificate($course, 'APROBADO');
                    if ($status_obsequi_aprobadoculminado) {
                        $status_certifi_aprobadoculminado++;
                        $course_por_aprobadoculminado[] = $course;
                    }
                    // linea condicion 100
                    $status_obsequiReprobado = self::getCourseByCertificate($course, 'REPROBADO');
                    if ($status_obsequiReprobado)
                        $cant_coursesreprobado++;

                    // LINEA CONDICION 98
                    if (($course['certifaction_test_original'] == 'APROBADO') &&
                        ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
                    ) {
                        $codicion_98[] = $course;
                    }
                }
            }

            $has_excelreprobadoculminado = self::excelreprobado($excel);
            if ($student_condition && $has_excelreprobadoculminado) {
                $cant_coursesreprobado++;
            }

            // Condicion  linea 91, retorna los cursos obsequio de un mes
            $curso_obsequio = [];
            foreach ($one_month as $course) {
                if (isset($course['certifaction_test_original']))
                    $curso_obsequio[] = $course;
            }

            //linea 88 COMIENZO
            if (sizeof($course_no_culminado) > 0)
                $text .= '👀 *OJO: recuerda que no culminaste:*' . "\n";
            //linea 89
            $text .= self::setCoursesName($course_no_culminado);
            //linea 90 y 91
            if (($student['AULA SAP'] == 'cursando' || $student['AULA SAP'] == 'completa') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($course_no_culminado)
            ) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o
                más cursos *reprobados o abandonados,* y
                no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            //linea 93 y 94
            if (sizeof($course_por_habilitarculminado) > 0) {
                $text .= self::setCourses($course_por_habilitarculminado, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //linea 95 y 96
            if ((sizeof($course_por_cursandoculminado) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_96) {
                    $text .= self::setCoursesName($codicion_96);
                }
            }
            // linea 97 y 98
            if ((sizeof($course_por_aprobadoculminado) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_98) {
                    $text .= self::setCoursesName($codicion_98);
                }
            }
            // linea 100 REvisar, me trae el excel solamente!!!
            if (($student['AULA SAP'] == 'cursando')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobado . ') cursos reprobados/abandonados,
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }
            //102 y 103
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,*
                y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            // 105 y 106
            if (sizeof($course_por_habilitarculminado) > 0) {
                $text .= self::setCourses($course_por_habilitarculminado, 'A pesar de quedar pendiente, no podrás habilitar:');
            }

            //107 y 108
            if ((sizeof($course_por_cursandoculminado) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_96) {
                    $text .= self::setCoursesName($codicion_96);
                }
            }

            //linea 109 y 110
            if ((sizeof($course_por_aprobadoculminado) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_98) {
                    $text .= self::setCoursesName($codicion_98);
                }
            }

            //linea 112
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobado . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            $course_abandonado = self::hasCourseAbandonado($student['courses']);
            $status_por_habilitarAbandono = 0;
            $status_por_cursandoAbandono = 0;
            $status_certifi_aprobadoAbandono = 0;
            $cant_coursesreprobadoAbandono = 0;
            $course_por_habilitarabandono = [];
            $course_por_cursandoAbandono = [];
            $course_por_aprobadoAbandono = [];
            $codicion_123 = [];
            $codicion_125 = [];

            foreach ($course_abandonado as $key => $course) {
                if (isset($course['certifaction_test_original'])) {

                    $has_status_por_habilitarabandono = self::getCourseByStatus($course, 'POR HABILITAR');
                    if ($has_status_por_habilitarabandono) {
                        $status_por_habilitarAbandono++;
                        $course_por_habilitarabandono[] = $course;
                    }

                    $has_status_cursandoabandono = self::getCourseByStatus($course, 'CURSANDO');
                    if ($has_status_cursandoabandono) {
                        $status_por_cursandoAbandono++;
                        $course_por_cursandoAbandono[] = $course;
                    }
                    //linea 123
                    if (($course['course_status_original'] == 'CURSANDO') &&
                        ($course['month_days'] != $course['diff_days'])
                    ) {
                        $codicion_123[] = $course;
                    }

                    $status_obsequi_aprobadoabandono = self::getCourseByCertificate($course, 'APROBADO');
                    if ($status_obsequi_aprobadoabandono) {
                        $status_certifi_aprobadoAbandono++;
                        $course_por_aprobadoAbandono[] = $course;
                    }

                    $status_obsequiReprobadoAbandono = self::getCourseByCertificate($course, 'REPROBADO');
                    if ($status_obsequiReprobadoAbandono)
                        $cant_coursesreprobadoAbandono++;

                    // linea 125
                    if (($course['certifaction_test_original'] == 'APROBADO') &&
                        ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
                    ) {
                        $codicion_125[] = $course;
                    }
                }
            }

            $has_excelreprobadoAbandono = self::excelreprobado($excel);
            if ($student_condition && $has_excelreprobadoAbandono) {
                $cant_coursesreprobadoAbandono++;
            }

            //linea 115 COMIENZO
            if (sizeof($course_abandonado) > 0)
                $text .= '👀 *OJO: recuerda que no culminaste:*' . "\n";
            //linea 116
            $text .= self::setCoursesName($course_abandonado);
            //linea 117 Y 118
            if (($student['AULA SAP'] == 'cursando' || $student['AULA SAP'] == 'completa') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($course_abandonado)
            ) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y
            y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            //120 y 121
            if (sizeof($course_por_habilitarabandono) > 0) {
                $text .= self::setCourses($course_por_habilitarabandono, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //122 y 123
            if ((sizeof($course_por_cursandoAbandono) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_123) {
                    $text .= self::setCoursesName($codicion_123);
                }
            }
            // linea 124 y 125
            if ((sizeof($course_por_aprobadoAbandono) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_125) {
                    $text .= self::setCoursesName($codicion_125);
                }
            }
            //linea 127
            if (($student['AULA SAP'] == 'cursando')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobadoAbandono . ') cursos reprobados/abandonados,
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }
            //linea 129 y 130
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,*
                y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            //linea 132 y 133
            if (sizeof($course_por_habilitarabandono) > 0) {
                $text .= self::setCourses($course_por_habilitarabandono, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //linea 134 y 135
            if ((sizeof($course_por_cursandoAbandono) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_123) {
                    $text .= self::setCoursesName($codicion_123);
                }
            }
            //linea 136 y 137
            if ((sizeof($course_por_aprobadoAbandono) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_125) {
                    $text .= self::setCoursesName($codicion_125);
                }
            }
            //linea 139
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobadoAbandono . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            $course_porhabilitar = self::hasCourseHabilitar($student['courses']);
            $status_por_cursandohabilitar = 0;
            $status_certifi_aprobadohabilitar = 0;
            $cant_coursesreprobadoHabilitar = 0;
            $course_por_cursandohabilitar = [];
            $course_por_aprobadohabilitar = [];
            $codicion_145 = [];
            $codicion_149 = [];
            $codicion_151 = [];

            foreach ($course_porhabilitar as $key => $course) {
                if (isset($course['certifaction_test_original'])) {
                    // linea 145
                    if ($course['course_status_original'] == 'ABANDONÓ' || $course['course_status_original'] == 'ABANDONO') {
                        $codicion_145[] = $course;
                    }
                    // linea 148
                    $has_status_cursandohabilitar = self::getCourseByStatus($course, 'CURSANDO');
                    if ($has_status_cursandohabilitar) {
                        $status_por_cursandohabilitar++;
                        $course_por_cursandohabilitar[] = $course;
                    }

                    $status_obsequi_aprobadohabilitar = self::getCourseByCertificate($course, 'APROBADO');
                    if ($status_obsequi_aprobadohabilitar) {
                        $status_certifi_aprobadohabilitar++;
                        $course_por_aprobadohabilitar[] = $course;
                    }

                    $status_obsequiReprobadohabilitar = self::getCourseByCertificate($course, 'REPROBADO');
                    if ($status_obsequiReprobadohabilitar)
                        $cant_coursesreprobadoHabilitar++;

                    if (($course['course_status_original'] == 'CURSANDO') &&
                        ($course['month_days'] != $course['diff_days'])
                    ) {
                        $codicion_149[] = $course;
                    }

                    if (($course['certifaction_test_original'] == 'APROBADO') &&
                        ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
                    ) {
                        $codicion_151[] = $course;
                    }
                }
            }
            $has_excelreprobadohabilitar = self::excelreprobado($excel);
            if ($student_condition && $has_excelreprobadohabilitar) {
                $cant_coursesreprobadoHabilitar++;
            }


            //Linea 143 COMIENZO
            if (sizeof($course_porhabilitar) > 0)
                $text .= '👀 *OJO tienes por habilitar:*' . "\n";
            //linea 144 y 146
            $text .= self::setCoursesName($course_porhabilitar);
            // linea 145 y 146
            if (($student['AULA SAP'] == 'cursando' || $student['AULA SAP'] == 'completa') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($codicion_145)
            ) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y
            y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            // linea 148 y 149
            if ((sizeof($course_por_cursandohabilitar) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_149) {
                    $text .= self::setCoursesName($codicion_149);
                }
            }
            // linea 150 y 151
            if ((sizeof($course_por_aprobadohabilitar) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_151) {
                    $text .= self::setCoursesName($codicion_151);
                }
            }
            // linea 153
            if (($student['AULA SAP'] == 'cursando')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobadoHabilitar . ') cursos reprobados/abandonados,
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }
            //linea 155 y 156
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= self::setCourses($curso_obsequio, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,*
                y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:');
            }
            //linea 158 y 159
            if ((sizeof($course_por_cursandohabilitar) > 0)) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_149) {
                    $text .= self::setCoursesName($codicion_149);
                }
            }
            // linea 160 y 161
            if ((sizeof($course_por_aprobadohabilitar) > 0)) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . $salto;
                if ($codicion_151) {
                    $text .= self::setCoursesName($codicion_151);
                }
            }
            // linea 163
            if ($student['EXAMEN'] == 'reprobado' || ($student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobadoHabilitar . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            $text .= '*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*' . $salto;
            $text .= '📌 Ya que este pago, lo debes realizar antes del: ' . $salto;
            foreach ($curso_obsequio as $course) {
                $text .= '-' . $course['end'] . "\n";
            }

            $text .= '⚠️ Recuerda que este día, se eliminarán tus accesos de manera automática a las 23:59. ' . $salto;
            $text .= '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*' . $salto;
            $text .= 'Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.' . $salto;
        }

        return $text;
    }

    public static function getHeaderLine($diff_day, $cant)
    {
        $text = '';
        switch ($diff_day) {
            case 1: {
                    if ($cant > 1)
                        $text .= "Frase Singular";
                    else
                        $text .= "Frase Plural";
                }
                break;
            case 4: {
                    if ($cant > 1)
                        $text .= "Frase Singular";
                    else
                        $text .= "Frase Plural";
                }
                break;
            case 7: {
                    if ($cant > 1)
                        $text .= "Frase Singular";
                    else
                        $text .= "Frase Plural";
                }
                break;
            case 15: {
                    if ($cant > 1)
                        $text .= "Frase Singular";
                    else
                        $text .= "Frase Plural";
                }
                break;
            default: {
                    if ($cant > 1)
                        $text .=     'Están por vencer tus cursos:';
                    else
                        $text .=     'Está por vencer tu curso:';
                    break;
                }
                return $text;
        }
    }

    public static function hasCourseCursando($courses)
    {
        $has_course = false;
        foreach ($courses as $key => $course) {
            if ($has_course == true)
                continue;
            if (isset($course['certifaction_test_original'])) {
                if ($course['course_status_original'] == 'CURSANDO' && $course['type'] == 'free') {
                    $has_course = true;
                }
            }
        }

        return $has_course;
    }

    public static function hasCoursenoCulminado($courses)
    {

        $has_course = [];
        foreach ($courses as $key => $course) {
            if (isset($course['certifaction_test_original'])) {
                if (
                    ($course['course_status_original'] == 'NO CULMINÓ' || $course['course_status_original'] == 'NO CULMINO') &&
                    $course['type'] == 'free' &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
                ) {
                    $has_course[] = $course;
                }
            }
        }

        return $has_course;
    }

    public static function hasCourseHabilitar($courses)
    {
        $has_course = [];
        foreach ($courses as $key => $course) {
            if (isset($course['certifaction_test_original'])) {
                if ($course['course_status_original'] == 'POR HABILITAR' && $course['type'] == 'free') {
                    $has_course[] = $course;
                }
            }
        }

        return $has_course;
    }

    public static function hasCourseAbandonado($courses)
    {
        $has_course = [];
        foreach ($courses as $key => $course) {
            if (isset($course['certifaction_test_original'])) {
                if (
                    ($course['course_status_original'] == 'ABANDONÓ' || $course['course_status_original'] == 'ABANDONO') &&
                    $course['type'] == 'free' &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
                ) {
                    $has_course[] = $course;
                }
            }
        }

        return $has_course;
    }

    public static function courseReprobado($courses)
    {
        $has_course = [];
        foreach ($courses as $key => $course) {
            if (isset($course['certifaction_test_original'])) {
                if (
                    ($course['certifaction_test_original'] == 'REPROBADO' ||
                        $course['certifaction_test_original'] == 'Intentos Pendientes') &&
                    $course['type'] == 'free' &&
                    ($course['month_days'] != $course['diff_days'])
                ) {
                    $has_course[] = $course;
                }
            }
        }
        return $has_course;
    }

    public static function excelreprobado($excel)
    {
        $has_excelreprobado = false;

        $has_excelreprobado = $excel['nivel_basico']['certifaction_test_original'] == 'REPROBADO'
            || $excel['nivel_intermedio']['certifaction_test_original'] == 'REPROBADO'
            || $excel['nivel_avanzado']['certifaction_test_original'] == 'REPROBADO';

        return $has_excelreprobado;
    }
    public static function getCourseByStatus($course, $status)
    {
        if ($course['course_status_original'] == $status)
            return true;
        return false;
    }
    public static function getCourseByCertificate($course, $status)
    {
        if ($course['certifaction_test_original'] == $status)
            return true;
        return false;
    }

    public static function setCourses($courses, $string)
    {
        $text = $string . "\n";
        $text .= self::setCoursesName($courses);

        return $text;
    }

    public static function setCoursesName($courses)
    {
        $text = '';
        foreach ($courses as $course) {
            $text .= $course['name'] . "\n";
        }
        return $text;
    }



    public function replaceText($text, $diff){

        $str = [
            [
                'orginal' => '🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.',
                '15'      => '⚠️ Informamos que este curso no ha cumplido con los estándares requeridos y, lamentablemente, no emitimos certificados por la participación en él.',
                '5'       => '🔔 Aviso importante: Este curso no ha alcanzado los criterios de aprobación necesarios. Por este motivo, no podemos ofrecer certificados de participación.',
                '1'       => ' Importante: Este curso no ha superado la evaluación requerida y, por lo tanto, no se emitirán certificados para los participantes.',
            ],
            [
                'original' => '🚩 🚩 Pero no todo está perdido. Puedes realizar el pago para ponderar los intentos de examen que reprobaste',
                '15'       => '💡💡 A pesar de los obstáculos, aún puedes cambiar el resultado. Realiza un pago para reexaminar los intentos de prueba que no fueron exitosos.',
                '5'        => '🌟🌟 No todo está decidido aún. Tienes la oportunidad de hacer un pago para reevaluar los intentos fallidos en tus exámenes.',
                '1'        => '✨✨ Aunque la situación es desafiante, aún hay una solución. Considera la opción de pagar para reconsiderar los exámenes que no aprobaste.',

            ]
        ];

        foreach($str as $key => $value){
            if($key == $diff){
                $text = str_replace($value['orginal'], $value[$diff], $text);
            }
        }
        return $text;
    }
}
