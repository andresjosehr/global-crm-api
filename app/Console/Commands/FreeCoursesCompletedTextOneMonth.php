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
            foreach ($one_month as $course) {
                $text .= $course['name'] . "\n";
            }
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
            $cursos_reprobados = 0; // esto es de la linea 63 
            $status_por_habilitar = 0;
            $status_certifi_aprobado = 0;
            $cant_courses_reprobado = 0;
            $status_por_cursando = 0;

            $has_course_reprobado = self::hasCourseReprobado($student['courses']);

            if ($has_course_reprobado)
                $text .= '👀 *OJO completaste, pero reprobaste:* ' . "\n";

            foreach ($student['courses'] as $key => $course) {
                if (isset($course['certifaction_test_original'])) {
                    //LINEA 62
                    $course['certifaction_test_original'] = strtolower($course['certifaction_test_original']);
                    $student['EXAMEN'] = strtolower($student['EXAMEN']);
                    $student['PONDERADO SAP'] = strtolower($student['PONDERADO SAP']);
                    $student['AULA SAP'] = strtolower($student['AULA SAP']);


                    if (($course['certifaction_test_original'] == 'sin intentos pendientes' || $course['certifaction_test_original'] == 'reprobado') &&
                        ($course['month_days'] != $course['diff_days']) ||
                        (($course['start'] == null) && ($course['end'] == null))
                    ) {
                        $text .= '-' . $course['name'] . "\n";
                    }
                    // //linea 64
                    if (($course['month_days'] == $course['diff_days'])) {
                        $text .= '-' . $course['name'] . "\n";
                    }

                    //linea 66
                    $has_status_por_habilitar = self::getCourseByStatus($course, 'POR HABILITAR');
                    if ($has_status_por_habilitar)
                        $status_por_habilitar++;

                    // linea 68
                    $has_status_cursando = self::getCourseByStatus($course, 'CURSANDO');
                    if ($has_status_cursando)
                        $status_por_cursando++;


                    // linea 70 
                    $status_obsequi_aprobado = self::getCourseByCertificate($course, 'aprobado');
                    if ($status_obsequi_aprobado)
                        $status_certifi_aprobado++;

                    // linea 73   
                    $status_obsequi_reprobado = self::getCourseByCertificate($course, 'reprobado');
                    if ($status_obsequi_reprobado)
                        $cant_courses_reprobado++;

                    //liena 67
                    if ($course['course_status_original'] == 'POR HABILITAR') {
                        $text .= '-' . $course['name'] . "\n";
                    }

                    //linea 69 
                    if ($course['course_status_original'] == 'CURSANDO' && $course['month_days'] != $course['diff_days']) {
                        $text .= '-' . $course['name'] . "\n";
                    }

                    //linea 71 
                    if (($course['certifaction_test_original'] == 'aprobado' && $course['month_days'] != $course['diff_days']) ||
                        ($course['start'] == null && $course['end'] == null)
                    ) {
                        $text .= '-' . $course['name'] . "\n";
                    }

                    //linea 76
                    if (($course['month_days'] == $course['diff_days'])) {
                        $text .= '-' . $course['name'] . "\n";
                    }
                    // linea 79
                    if ($course['course_status_original'] == 'POR HABILITAR') {
                        $text .= '-' . $course['name'] . "\n";
                    }
                    //linea 81
                    if (($course['course_status_original'] == 'CURSANDO') && $course['month_days'] != $course['diff_days']) {
                        $text .= '-' . $course['name'] . "\n";
                    }
                    //linea 83
                    if (($course['certifaction_test_original'] == 'aprobado' && $course['month_days'] != $course['diff_days']) ||
                        ($course['start'] == null && $course['end'] == null)
                    ) {
                        $text .= '-' . $course['name'] . "\n";
                    }
                }
                // de aqui... linea 63
                if (isset($course['certifaction_test_original'])) {
                    if (
                        ($student['AULA SAP'] == 'cursando' || $student['AULA SAP'] == 'completa') &&
                        ($student['CERTIFICADO'] != 'EMITIDO') &&
                        ($course['course_status_original'] == 'COMPLETA' &&
                            ($course['certifaction_test_original'] == 'sin intentos pendientes' || $course['certifaction_test_original'] == 'reprobado')) &&
                        ($course['month_days'] != $course['diff_days']) ||
                        (($course['start'] == null) && ($course['end'] == null))
                    ) {
                        $cursos_reprobados++;
                    }
                } //linea 63
            }

            //linea 63 continuacion de la condicion de excel
            $has_excelreprobado = self::excelreprobado($excel);
            if ($has_excelreprobado) {
                $cursos_reprobados++;
                $cant_courses_reprobado++;
            }

            if ($has_excelreprobado > 1) {
                $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* 
                y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:' . "\n";
            }
            /// aqui termina

            //linea 66
            if ($status_por_habilitar > 1)
                $text .= 'A pesar de quedar pendiente, no podrás habilitar:' . "\n";
            //linea 68
            if ($status_por_cursando > 1)
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . "\n";
            //linea 70
            if ($status_certifi_aprobado > 1)
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . "\n";

            //linea 73
            if ($student['AULA SAP'] == 'cursando')
                $text .= 'Ya que tendrías ('  . ($cant_courses_reprobado) .  ') cursos reprobados/abandonados, 
            así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . "\n";


            // linea  75
            if (($student['EXAMEN'] == 'reprobado' || $student['EXAMEN'] == 'sin intentos gratis')) {
                $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,*
                 y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:' . "\n";
            }

            //linea 78
            if ($status_por_habilitar > 1)
                $text .= 'A pesar de quedar pendiente, no podrás habilitar:' . "\n";

            // linea 80
            if ($has_status_cursando > 1)
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . "\n";

            //lineas 82
            if ($status_obsequi_aprobado > 1)
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional:' . "\n";

            //linea 85
            if ($student['AULA SAP'] == 'reprobado' || $student['AULA SAP'] == 'sin intentos gratis') {
                $text .= 'Ya que tendrías (' . $cant_courses_reprobado . ') cursos reprobados/abandonados, *siendo tu último procedimiento
                 con nosotros, porque no tendrías más cursos por habilitar.*';
            }
        }

        return $text;
    }

    public static function hasCourseCursando($courses)
    {
        $has_course = false;
        foreach ($courses as $key => $course) {
            if ($has_course == true)
                continue;
            if (isset($course['certifaction_test_original'])) {
                if ($course['course_status_original'] == 'CURSANDO') {
                    $has_course = true;
                }
            }
        }

        return $has_course;
    }

    public static function hasCourseReprobado($courses)
    {
        $has_course = false;
        foreach ($courses as $key => $course) {
            if ($has_course == true)
                continue;
            if (isset($course['certifaction_test_original'])) {
                if ($course['certifaction_test_original'] == 'REPROBADO' || $course['certifaction_test_original'] != 'Intentos Pendientes') {
                    $has_course = true;
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
}
