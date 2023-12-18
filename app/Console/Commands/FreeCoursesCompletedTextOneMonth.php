<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleSheetController;
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
            $data = new StudentsExcelController();
            $students = $data->index('test');
            //$students = json_decode(file_get_contents(storage_path('app/public/data.json')), true);
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
        $dataToUpdate = [];

        foreach ($students as $student) {
            foreach ($student['courses'] as $course) {
                $dataToUpdate[] = [
                    'sheet_id'          => $student['sheet_id'],
                    'course_row_number' => $student['course_row_number'],
                    'column'            => "BA",
                    'email'             => $student['CORREO'],
                    'tab_id'            => $student['course_tab_id'],
                    'value'             => $student['text'],
                ];
            }
        }



        $google_sheet = new GoogleSheetController();
        $data = $google_sheet->transformData($dataToUpdate);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return $this->line(json_encode($dataToUpdate));
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
                        if ($course['diff_days'] == $course['month_days'] &&  $course['type'] == 'free')
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
                } elseif ($excel['nivel_basico']['certifaction_test_original'] == 'APROBADO') {
                    $excel_level_apro++;
                }

                if ($excel['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $excel_siF++;
                    $excel_level_siF[] = ['name' => 'Intermedio'];
                } elseif ($excel['nivel_intermedio']['certifaction_test_original'] == 'APROBADO') {
                    $excel_level_apro++;
                }

                if ($excel['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis') {
                    $excel_siF++;
                    $excel_level_siF[] =  ['name' => 'Avanzado'];
                } elseif ($excel['nivel_avanzado']['certifaction_test_original'] == 'APROBADO') {
                    $excel_level_apro++;
                }
            }
        }


        $text .= '¡Hola!' . "\n";
        $text .= $student['NOMBRE'] . "\n";

        if (sizeof($one_month) == 1) {
            $text .= 'Está por vencer tu curso: ' . "\n";
        } else {

            $text .= 'Están por vencer tus cursos: ' . "\n";
        }
        // linea 28
        $text .= self::setCoursesName($one_month);

        //linea 30 y 31
        if ($mbi_and_msp_siF == 1) {
            $text .= ' 🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.' . "\n";
        } elseif ($mbi_and_msp_siF == 2) {
            $text .= '🚨 Actualmente estos cursos ';
        }

        // linea de la 32 a la 35 
        if ($mbi_and_msp_siF == 2 && $excel_siF > 0) {
            $text .= "*se encuentran reprobados y para Excel, debes aprobar los 3 niveles,* 
            porque no brindamos certificados por participación, ni por nivel independiente, y este es el estado de cada nivel del curso:" . "\n";
            $text .= "- NIVEL BASICO ESTADO: " . "\n";
            $text .= $excel['nivel_basico']['certifaction_test_original'] . "\n";
            $text .= "- NIVEL INTERMEDIO ESTADO:" . "\n";
            $text .= $excel['nivel_intermedio']['certifaction_test_original'] . "\n";
            $text .= "- NIVEL AVANZADO ESTADO: " . "\n";
            $text .= $excel['nivel_avanzado']['certifaction_test_original'] . "\n";
        } elseif ($mbi_and_msp_siF == 2) {
            $text .= 'se encuentran reprobados y no brindamos certificados por participación.' . "\n";
        }

        // linea 36 a 40
        if ($excel_siF > 0 && $mbi_and_msp_siF == 0) {
            $text .= "🚨 A continuación te indico el estado actual de cada nivel:" . "\n";
            $text .= "- NIVEL BASICO ESTADO: " . "\n";
            $text .= $excel['nivel_basico']['certifaction_test_original'] . "\n";
            $text .= "- NIVEL INTERMEDIO ESTADO:" . "\n";
            $text .= $excel['nivel_intermedio']['certifaction_test_original'] . "\n";
            $text .= "- NIVEL AVANZADO ESTADO: " . "\n";
            $text .= $excel['nivel_avanzado']['certifaction_test_original'] . "\n";
            $text .= 'Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente' . "\n";
        }

        // linea 42 y 43
        $text .= '🚩 🚩 *Pero no todo está perdido.*' . "\n";
        $text .= '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*' . "\n";

        // linea 44 y 45
        if ($excel_siF > 0) {
            if ($mbi_and_msp_siF > 0) {
                if ($mbi_and_msp_siF > 1) {
                    $text .= 'CURSOS y ';
                } elseif ($mbi_and_msp_siF == 1) {
                    $text .= 'CURSO y ';
                }
            }

            if ($excel_siF > 1) {
                $text .= 'NIVELES DE EXCEL SIN INTENTOS GRATIS' . "\n";
            } elseif ($excel_siF == 1) {
                $text .= 'NIVEL DE EXCEL SIN INTENTOS GRATIS' . "\n";
            }

            foreach ($couse_name_mpb_and_pbi as $mpb_pbi) {
                $text .= '-' . $mpb_pbi['name']  . "\n";
            }

            foreach ($excel_level_siF as $level) {
                $text .= '-Nivel ' . $level['name'] . ' excel' . "\n";
            }
        }


        // LINEA 46
        if (($student['AULA SAP'] == 'CURSANDO') && ($mbi_and_msp_siF > 0 || $excel_siF  > 0)) {
            $text .= 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.' . "\n";
        }

        // LINEA 47
        if (($student['EXAMEN'] == 'APROBADO') || (($student['PONDERADO SAP'] == 'PAGADO') && ($mbi_and_msp_siF > 0 || $excel_siF  > 0))) {
            $text .= 'Y de esta manera obtener tus certificados.' . "\n";
        }

        //falta la linea 48

        // linea 49, los cursos pbi o msp estan en $mbi_and_msp_siF si es > 0  puede ser mbp/ms  por lo tanto cualquier de los dos 
        if ($student['EXAMEN'] == 'APROBADO' || ($student['PONDERADO SAP'] == 'PAGADO' && $mbi_and_msp_siF > 0)) {
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

        //linea 54 a 58
        $has_course_cursando = self::hasCourseCursando($student['courses']);
        $condicion_58 = [];
        if ($has_course_cursando) {
            $text .= '👀 OJO aún estás cursando:' . "\n";
            //linea 55
            $text .= self::setCoursesName($has_course_cursando);

            foreach ($has_course_cursando as $key => $course) {
                // linea condicion 58, nombre de los cursos obsequio que terminan en un mes
                if ($course['month_days'] == $course['diff_days']) {
                    $condicion_58[] = $course;
                }
            }

            //linea 56
            if (
                ($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($has_course_cursando)
            ) {
                $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados*, 
                        y aún no te certificas en SAP. Por lo que podrías perder el acceso, a pesar de haber iniciado, si no pagas el ponderado de: ' . "\n";
            }

            //linea 57
            if (($student['EXAMEN'] == 'REPROBADO' || $student['EXAMEN'] == 'Sin Intentos Gratis') &&
                ($has_course_cursando)
            ) {
                $text .= 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP.
                     Por lo que está en peligro el acceso, si no pagas el ponderado de: ' . "\n";
            }
            // linea 58
            $text .= self::setCoursesName($condicion_58);
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
        $student_condition = ($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') && ($student['CERTIFICADO'] != 'EMITIDO');
        $course_sin_intentos_o_reprobado = [];
        $codicion_63 = [];
        $codicion_69 = [];
        $condicion_71 = [];



        foreach ($course_reprobado as $key => $course) {
            //LINEA 62
            if (
                $course_reprobado &&
                ($course['month_days'] != $course['diff_days']) ||
                (($course['start'] == null) && ($course['end'] == null))
            ) {
                $course_sin_intentos_o_reprobado[] = $course;
            }

            if (($student_condition)  &&  $course_reprobado &&
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
            $status_obsequi_aprobado = self::getCourseByCertificate($course, 'APROBADO');
            if ($status_obsequi_aprobado) {
                $status_certifi_aprobado++;
                $course_por_aprobado[] = $course;
            }


            // linea 73   
            $status_obsequi_reprobado = self::getCourseByCertificate($course, 'REPROBADO');
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
            if (isset($course['certifaction_test_original'])) {
                if (
                    $course['certifaction_test_original'] == 'APROBADO' &&
                    $course['month_days'] != $course['diff_days']
                ) {
                    $condicion_71[] = $course;
                }
            } else {
                $nivelbasico = $course['nivel_basico']['certifaction_test_original'] == 'APROBADO';
                $nivelintermedio = $course['nivel_intermedio']['certifaction_test_original'] == 'APROBADO';
                $nivelavanzado = $course['nivel_avanzado']['certifaction_test_original'] == 'APROBADO';

                if (
                    $nivelbasico || $nivelintermedio || $nivelavanzado &&
                    $course['month_days'] != $course['diff_days']
                ) {
                    $condicion_71[] = $course;
                }
            }
        }

        //linea 61 COMIENZO
        if (sizeof($course_reprobado) > 0) {
            $text .= '👀 *OJO completaste, pero reprobaste:* ' . "\n";
            //linea 62
            $text .= self::setCoursesName($course_sin_intentos_o_reprobado);

            //linea 63 y 64
            if (sizeof($codicion_63) > 0) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. 
                Por lo que si no realizas el pago del ponderado de: ');
            }

            //linea 66 67
            if ($status_por_habilitar > 0) {
                $text .= self::setCourses($course_por_habilitar, 'A pesar de quedar pendiente, no podrás habilitar:');
            }
            //linea 68 y 69
            if (sizeof($course_por_cursando) > 0) {
                $text .= 'A pesar de haber iniciado, perderías el acceso a:' . $salto;
                if ($codicion_69) {
                    $text .= self::setCoursesName($codicion_69);
                }
            }

            //linea 70 y 71

            if ($status_certifi_aprobado > 0) {
                $text .= 'A pesar de haber aprobado, perderías el acceso al certificado internacional: ' . $salto;
                if ($condicion_71) {
                    $text .= self::setCoursesName($condicion_71);
                }
            }

            // linea 73
            if ($student['AULA SAP'] == 'CURSANDO') {
                $text .= 'Ya que tendrías (' . $cant_courses_reprobado . ') cursos reprobados/abandonados, así que 
                *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }


            // linea 75 Y 76
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
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
                if ($condicion_71) {
                    $text .= self::setCoursesName($condicion_71);
                }
            }

            //linea 85
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= 'Ya que tendrías (' . $cant_courses_reprobado . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }
        }


        $course_no_culminado = self::hasCoursenoCulminado($student['courses']);
        $status_por_habilitarculminado = 0;
        $status_por_cursandoculminado = 0;
        $status_certifi_aprobadoculminado = 0;
        $cant_coursesreprobado = 0;
        $course_por_aprobadoculminado = [];
        $course_por_habilitarculminado = [];
        $course_por_cursandoculminado = [];
        $condicion_89 = [];
        $codicion_96 = [];
        $condicion_98 = [];

        foreach ($student['courses'] as $key => $course) {
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
                ($course['month_days'] != $course['diff_days']) && $course['type'] == 'free'
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
            if (isset($course['certifaction_test_original'])) {
                if (
                    $course['certifaction_test_original'] == 'APROBADO' &&
                    $course['month_days'] != $course['diff_days'] &&  $course['type'] == 'free'
                ) {
                    $condicion_98[] = $course;
                }
            } else {
                $nivelbasico = $course['nivel_basico']['certifaction_test_original'] == 'APROBADO';
                $nivelintermedio = $course['nivel_intermedio']['certifaction_test_original'] == 'APROBADO';
                $nivelavanzado = $course['nivel_avanzado']['certifaction_test_original'] == 'APROBADO';

                if (
                    $nivelbasico || $nivelintermedio || $nivelavanzado &&
                    $course['month_days'] != $course['diff_days'] &&  $course['type'] == 'free'
                ) {
                    $condicion_98[] = $course;
                }
            }


            if (($course['course_status_original'] == 'NO CULMINÓ' || $course['course_status_original'] == 'NO CULMINO'
            ) &&  $course['type'] == 'free') {
                $condicion_89[] = $course;
            }
        }


        //linea 88 COMIENZO
        if (($course_no_culminado)) {
            $text .= '👀 *OJO: recuerda que no culminaste:*' . "\n";
            //linea 89
            $text .= self::setCoursesName($condicion_89);

            //linea 90 y 91
            if (($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($condicion_89)
            ) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o 
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
                if ($condicion_98) {
                    $text .= self::setCoursesName($condicion_98);
                }
            }

            // linea 100 REvisar, me trae el excel solamente!!!
            if (($student['AULA SAP'] == 'CURSANDO')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobado . ') cursos reprobados/abandonados, 
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            //102 y 103
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* 
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
                if ($condicion_98) {
                    $text .= self::setCoursesName($condicion_98);
                }
            }

            //linea 112
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobado . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }
        }

        $course_abandonado = self::hasCourseAbandonado($student['courses']);
        $status_por_habilitarAbandono = 0;
        $status_por_cursandoAbandono = 0;
        $status_certifi_aprobadoAbandono = 0;
        $cant_coursesreprobadoAbandono = 0;
        $course_por_habilitarabandono = [];
        $course_por_cursandoAbandono = [];
        $course_por_aprobadoAbandono = [];
        $condicion_116 = [];
        $codicion_123 = [];
        $codicion_125 = [];

        foreach ($student['courses'] as $key => $course) {


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

            // lines condicion 116
            if (
                ($course['course_status_original'] == 'ABANDONÓ' || $course['course_status_original'] == 'ABANDONO') &&
                $course['type'] == 'free' &&
                ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
            ) {
                $condicion_116[] = $course;
            }


            //linea 123
            if (($course['course_status_original'] == 'CURSANDO') && $course['type'] == 'free' &&
                ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
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
            if (isset($course['certifaction_test_original'])) {
                if (
                    $course['certifaction_test_original'] == 'APROBADO' &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null)) &&  $course['type'] == 'free'
                ) {
                    $codicion_125[] = $course;
                }
            } else {
                $nivelbasico = $course['nivel_basico']['certifaction_test_original'] == 'APROBADO';
                $nivelintermedio = $course['nivel_intermedio']['certifaction_test_original'] == 'APROBADO';
                $nivelavanzado = $course['nivel_avanzado']['certifaction_test_original'] == 'APROBADO';

                if (
                    $nivelbasico || $nivelintermedio || $nivelavanzado &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null)) &&  $course['type'] == 'free'
                ) {
                    $codicion_125[] = $course;
                }
            }
        }



        //linea 115 COMIENZO
        if ($course_abandonado) {
            $text .= '👀 *OJO: recuerda que abandonaste:*' . "\n";
            //linea 116
            $text .= self::setCoursesName($condicion_116);

            //linea 117 Y 118
            if (($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($condicion_116)
            ) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y
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
            if (($student['AULA SAP'] == 'CURSANDO')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobadoAbandono . ') cursos reprobados/abandonados, 
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            //linea 129 y 130
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* 
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
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobadoAbandono . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }
        }

        $course_porhabilitar = self::hasCourseHabilitar($student['courses']);
        $status_por_cursandohabilitar = 0;
        $status_certifi_aprobadohabilitar = 0;
        $cant_coursesreprobadoHabilitar = 0;
        $course_por_cursandohabilitar = [];
        $course_por_aprobadohabilitar = [];
        $condicion_144 = [];
        $codicion_145 = [];
        $codicion_149 = [];
        $codicion_151 = [];



        foreach ($student['courses'] as $key => $course) {
            // linea 144
            if (
                $course['course_status_original'] == 'POR HABILITAR' &&
                $course['type'] == 'free'
            ) {
                $condicion_144[] = $course;
            }

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

            // condicion 151
            if (isset($course['certifaction_test_original'])) {
                if (
                    $course['certifaction_test_original'] == 'APROBADO' &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null)) &&  $course['type'] == 'free'
                ) {
                    $codicion_151[] = $course;
                }
            } else {
                $nivelbasico = $course['nivel_basico']['certifaction_test_original'] == 'APROBADO';
                $nivelintermedio = $course['nivel_intermedio']['certifaction_test_original'] == 'APROBADO';
                $nivelavanzado = $course['nivel_avanzado']['certifaction_test_original'] == 'APROBADO';

                if (
                    $nivelbasico || $nivelintermedio || $nivelavanzado &&
                    ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null)) &&  $course['type'] == 'free'
                ) {
                    $codicion_151[] = $course;
                }
            }
        }

        //Linea 143 COMIENZO
        if ($course_porhabilitar) {
            $text .= '👀 *OJO tienes por habilitar:*' . "\n";
            //linea 144 
            $text .= self::setCoursesName($condicion_144);
            // linea 145 y 146
            if (($student['AULA SAP'] == 'CURSANDO' || $student['AULA SAP'] == 'COMPLETA') &&
                ($student['CERTIFICADO'] != 'EMITIDO') && ($codicion_145)
            ) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y
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
            if (($student['AULA SAP'] == 'CURSANDO')) {
                $text .= 'Ya que tendrías (' . $cant_coursesreprobadoHabilitar . ') cursos reprobados/abandonados, 
                así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*' . $salto;
            }

            //linea 155 y 156
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= self::setCourses($one_month, 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* 
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
            if ($student['EXAMEN'] == 'REPROBADO' || ($student['EXAMEN'] == 'Sin Intentos Gratis')) {
                $text .= 'Ya que tendrías (' .  $cant_coursesreprobadoHabilitar . ') cursos reprobados/abandonados,
                 *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*' . $salto;
            }
        }

        $text .= '*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*' . $salto;
        $text .= '📌 Ya que este pago, lo debes realizar antes del: ' . $salto;
        foreach ($one_month as $course) {
            $text .= '- ' . $course['end'] . "\n";
        }

        $text .= '⚠️ Recuerda que este día, se eliminarán tus accesos de manera automática a las 23:59. ' . $salto;
        $text .= '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*' . $salto;
        $text .= 'Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.' . $salto;


        $text = self::replaceText($text, "7");

        return $text;
    }
    public static function replaceText($text, $diff)
    {

        $str = [
            //linea 1
            [
                'original' => '¡Hola!',
                '15' => '⚠️ ¡Atención urgente! ⏳',
                '7' => '🌟 ¡Importante actualización! 📢',
                '4' => '🔔 ¡Notificación crucial! 🚨',
                '1' => '¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:',
            ],

            //linea 2
            [
                'original' => 'Está por vencer tu curso:',
                '15'      => 'Está por vencer tu curso:',
                '7'       => 'Está por vencer tu curso:',
                '4'       => '¡Urgente, tu curso está en peligro! ⚠️',
                '1'       => 'Te envío la última información de tu curso:',

            ],
            [
                'original' => 'Están por vencer tus cursos:',
                '15'      => 'Están por vencer tus cursos:',
                '7'       => 'Están por vencer tus cursos:',
                '4'       => '¡Urgente, tus cursos están en peligro! ⚠️',
                '1'       => 'Te envío la última información de tus cursos:',

            ],
            [
                'original' => 'Están por vencer tus cursos:',
                '15'      => 'Están por vencer tus cursos:',
                '7'       => 'Están por vencer tus cursos:',
                '4'       => '¡Urgente, tus cursos están en peligro! ⚠️',
                '1'       => 'Te envío la última información de tus cursos:',

            ],
            [
                'original' => '🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.',
                '15'      => '🚨 Sé que reprobaste este curso y lamentablemente no brindamos certificados por participación.',
                '7'       => '🚨 Una vez más te indico que este curso está reprobado y que aún no has optado por realizar el pago del ponderado.',
                '4'       => '🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual  es una lástima, porque no contaremos con tu participación en esta certificación.',
                '1'       => 'Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado.*',

            ],
            [
                'original' => '🚨 Actualmente estos cursos se encuentran reprobados y no brindamos certificados por participación.',
                '15'      => '🚨 Sé que reprobaste estos cursos y lamentablemente no brindamos certificados por participación.',
                '7'       => '🚨 Una vez más te indico que estos cursos están reprobados y que aún no has optado por realizar el pago del ponderado.',
                '4'       => '🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no contaremos con tu participación en estas certificaciones.',
                '1'       => 'Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado.*',

            ],
            [
                'original' => '🚨 Actualmente estos cursos *se encuentran reprobados y para Excel, debes aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente, y este es el estado de cada nivel del curso:',
                '15'      => '🚨 Sé que reprobaste estos cursos, y *para Excel, debes aprobar los 3 niveles,* porque no brindamos certificados por nivel independiente, y este es el estado de cada nivel:',
                '7'       => '🚨 Una vez más te indico que estos cursos están reprobados y *para Excel, debes aprobar los 3 niveles,* y este es el estado de cada nivel:',
                '4'       => '🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no estaremos contando con tu participación en la certificación de estos cursos porque están reprobados, ya que Excel tiene:',
                '1'       => 'Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado,* porque culminaste con cada nivel de la siguiente manera:',

            ],
            [
                'original' => '🚨 A continuación te indico el estado actual de cada nivel:',
                '15'      => '🚨 Necesito que sepas el estado actual de cada nivel:',
                '7'       => '🚨 Necesito que sepas el estado actual de cada nivel:',
                '4'       => '🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no estaremos contando con tu participación en la certificación, porque reprobaste el nivel:',
                '1'       => 'Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado,* porque con Excel culminaste con cada nivel de la siguiente manera:',

            ],
            [
                'original' => 'Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente',
                '15'      => 'Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente. Así que, en este momento, el curso se encuentra *REPROBADO.*',
                '7'       => 'Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por nivel independiente. Así que en este momento, el curso se encuentra *REPROBADO,* ya que aún no has optado por el pago del ponderado.',
                '4'       => 'Y no ofrecemos certificado de participación por haber completado algún curso, ni por niveles independientes.',
                '1'       => 'Es decir, que *aunque hayas aprobado ese nivel, no recibirás certificación alguna porque la condición para certificar Excel Empresarial, es que hayas aprobado todos los niveles que lo comprenden.*',

            ],
            [
                'original' => '🚩 🚩 *Pero no todo está perdido.*',
                '15' => '🚩 🚩 *Pero todavía hay posibles soluciones:*',
                '7' => '🚩 🚩 Si aún estás considerando realizar tu pago, te recuerdo que debe ser en estos días, ya que la fecha fin es el:',
                '4' => '📆 🚩 *¡Importante recordatorio de fecha!*',
                '1' => '🚩 🚩 *Mensaje urgente:*',
            ],
            [
                'original' => '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*',
                '15' => '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*',
                '7' => '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*',
                '4' => '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*',
                '1' => '*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*',
            ],

            [
                'original' => 'CURSO y NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '15' => 'CURSO y NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '7' => 'CURSO y NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '4' => 'CURSO y NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '1' => 'CURSO y NIVEL DE EXCEL',
            ],
            [
                'original' => 'NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '15'      => 'NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '7'       => 'NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '4'       => 'NIVEL DE EXCEL "SIN INTENTOS GRATIS"',
                '1'       => 'NIVEL DE EXCEL',

            ],
            [
                'original' => 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.',
                '15' => 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.',
                '7' => 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.',
                '4' => 'Y de esta manera obtener tus certificados cuando te certifiques en SAP.',
                '1' => 'Obtener tus certificados cuando te certifiques en SAP.',
            ],

            [
                'original' => 'Y de esta manera obtener tus certificados.',
                '15' => 'Y de esta manera obtener tus certificados.',
                '7' => 'Y de esta manera obtener tus certificados',
                '4' => 'Y de esta manera obtener tus certificados',
                '1' => 'Obtener tus certificados',
            ],

            [
                'original' => 'Y de esta manera obtener tu certificado.',
                '15'      => 'Y de esta manera obtener tu certificado.',
                '7'       => 'Y de esta manera obtener tu certificado.',
                '4'       => 'Y no ofrecemos certificado de participación por haber completado algún curso, ni por niveles independientes.',
                '1'       => 'Es decir, que *aunque hayas aprobado ese nivel, no recibirás certificación alguna porque la condición para certificar Excel Empresarial, es que hayas aprobado todos los niveles que lo comprenden.*',

            ],
            [
                'original' => '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobado un nivel, no pierdas la oportunidad.',
                '15'      => '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobado un nivel, no pierdas la oportunidad.',
                '7'       => 'Has avanzado significativamente. Mantén ese impulso y sigue adelante hacia tus metas.',
                '4'       => 'Cada paso que tomas te acerca más al éxito. ¡Sigue así y alcanzarás tus objetivos!',
                '1'       => 'Bien hecho por comenzar este viaje. Aprovecha esta oportunidad para aprender y crecer.',
            ],
            [
                'original' => '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobados dos niveles, no pierdas la oportunidad.',
                '15'      => '¡Estás a tan solo un paso de lograrlo! Ya tienes aprobados dos niveles, no pierdas la oportunidad.',
                '7'       => 'Tu dedicación se refleja en tus logros. Sigue avanzando con confianza hacia nuevos desafíos.',
                '4'       => 'Dos niveles completados, ¡fantástico! Continúa construyendo tu éxito con cada paso que das.',
                '1'       => 'Cada inicio es un logro en sí mismo. Estás en el camino correcto, sigue avanzando con determinación.',
            ],

            [
                'original' => '👀 *OJO aún estás cursando:*',
                '15'      => '👀 *OJO, como aún no te has certificado en SAP y aún estás cursando:*',
                '7'       => '👀 *Como aún no te has certificado en SAP y aún estás cursando:*',
                '4'       => '👀 *OJO aún estás cursando:*',
                '1'       => 'Por lo que, al tener cursos reprobados, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que podrías perder el acceso, a pesar de haber iniciado, si no pagas el ponderado de:',
                '15'      => 'Esto significa que, incluso si has comenzado, corres el riesgo de perder el acceso si no completas el pago del ponderado de:',
                '7'       => 'Esto significa que, incluso si has comenzado, corres el riesgo de perder el acceso si no completas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que podrías perder el acceso, a pesar de haber iniciado, si no pagas el ponderado de:',
                '1'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP.',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro el acceso, si no pagas el ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro el acceso, si no pagas el ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro el acceso, si no pagas el ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro el acceso, si no pagas el ponderado de:',
                '1'       => 'Como aún no te certificas en SAP, al haber reprobado solo un curso, aún mantienes el acceso a:',

            ],
            [
                'original' => '👀 *OJO completaste, pero reprobaste:*',
                '15'      => '👀 *OJO como aún no te has certificado en SAP y completaste, pero reprobaste:*',
                '7'       => '👀 *Como aún no te has certificado en SAP y completaste, pero reprobaste:*',
                '4'       => '👀 *OJO completaste, pero reprobaste:*',
                '1'       => 'Por lo que, al haber reprobado SAP y también:',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Si no realizas el pago del ponderado de:',
                '7'       => 'Si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Recuerda la importancia de cumplir con el pago del ponderado para asegurar tu progreso académico. Evita tener pendientes dos o más cursos reprobados o abandonados y certifícate en SAP. Tu compromiso con el pago del ponderado de: ',

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'A pesar de quedar pendiente, no podrás habilitar:',
                '7'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'A pesar de quedar pendiente, no podrás habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'A pesar de haber iniciado, perderías el acceso a:',
                '7'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'A pesar de haber iniciado, pierdes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15'      => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '7'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '4'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1'       => 'A pesar de haber aprobado, pierdes el acceso al certificado internacional:',

            ],
            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Como aún no te certificas en SAP, al haber reprobado estos  cursos:',

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'A pesar de quedar pendiente, no podrás habilitar:',
                '7'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'No podrás habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'A pesar de haber iniciado, perderías el acceso a:',
                '7'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'Pierdes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15'      => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '7'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '4'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1'       => 'Pierdes el acceso al certificado internacional:',

            ],
            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => '👀 *OJO: recuerda que no culminaste:*',
                '15'      => '👀 *OJO: como aún no te has certificado en SAP y no culminaste:*',
                '7'       => '👀 *Como aún no te has certificado en SAP y no culminaste:*',
                '4'       => '👀 *OJO: recuerda que no culminaste:*',
                '1'       => 'Como aún no te certificas en SAP, reprobaste el curso:', // improvisado

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Si no realizas el pago del ponderado de:',
                '7'       => 'Si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Por lo que, como también reprobaste y no culminaste:', // improvisado

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'A pesar de quedar pendiente, no podrás habilitar:',
                '7'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'No puedes habilitar: ',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'A pesar de haber iniciado, perderías el acceso a:',
                '7'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'Pierdes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15'      => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '7'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '4'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1'       => 'No tendrás el certificado internacional:',

            ],
            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. ',

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'Aunque esté pendiente, la habilitación no será posible:',
                '7'       => 'No será posible habilitar, incluso si queda pendiente:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'no podrás habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'Aunque hayas comenzado, perderías el acceso a:',
                '7'       => 'Incluso después de iniciar, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'perderías el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15' => 'Incluso habiendo aprobado, perderías el acceso al certificado internacional:',
                '7' => 'Aprobación no garantiza acceso al certificado internacional:',
                '4' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1' => 'Perderías el acceso al certificado internacional:',
            ],

            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => '👀 *OJO: recuerda que abandonaste:*',
                '15'      => '👀 *OJO: como aún no te has certificado en SAP y abandonaste:*',
                '7'       => '👀 *Como aún no te has certificado en SAP y abandonaste:*',
                '4'       => '👀 *OJO: recuerda que abandonaste:*',
                '1'       => '👀 *OJO: recuerda que abandonaste:*',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Como aún no te certificas en SAP, reprobaste  y abandonaste:',

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'A pesar de quedar pendiente, no podrás habilitar:',
                '7'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'No puedes habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'A pesar de haber iniciado, perderías el acceso a:',
                '7'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'Pierdes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15'      => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '7'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '4'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1'       => 'No tendrás el certificado internacional:',

            ],
            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP.',

            ],
            [
                'original' => 'A pesar de quedar pendiente, no podrás habilitar:',
                '15'      => 'Aunque esté pendiente, no podrás habilitar:',
                '7'       => 'A pesar de estar pendiente, la habilitación no será posible:',
                '4'       => 'A pesar de quedar pendiente, no podrás habilitar:',
                '1'       => 'No podrás habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'Aunque hayas iniciado, perderías el acceso a:',
                '7'       => 'Incluso después de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'Piedes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15' => 'Incluso habiendo aprobado, perderías el acceso al certificado internacional:',
                '7' => 'Aprobación no garantiza acceso al certificado internacional:',
                '4' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1' => 'A pesar de aprobar, perderías el acceso al certificado internacional:'
            ],
            [
                'original' => 'Ya que tendrías ( ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías ( ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías ( ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => '👀 *OJO tienes por habilitar:*',
                '15'      => '👀 *OJO como aún no te has certificado en SAP y tienes por habilitar:*',
                '7'       => '👀 *Como aún no te has certificado en SAP y tienes por habilitar:*',
                '4'       => '👀 *OJO tienes por habilitar:*',
                '1'       => 'Como aún no te certificas en SAP y reprobaste el  cursos',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'No podrás habilitar:',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15'      => 'A pesar de haber iniciado, perderías el acceso a:',
                '7'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '4'       => 'A pesar de haber iniciado, perderías el acceso a:',
                '1'       => 'Pierdes el acceso a:',

            ],
            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15'      => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '7'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '4'       => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1'       => 'Pierdes el certificado internacional:',

            ],
            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías ( ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '15'      => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '7'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '4'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:',
                '1'       => 'Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. ',

            ],
            [
                'original' => 'A pesar de haber iniciado, perderías el acceso a:',
                '15' => 'Aunque hayas iniciado, perderías el acceso a:',
                '7' => 'Incluso después de haber iniciado, perderías el acceso a:',
                '4' => 'A pesar de haber iniciado, perderías el acceso a:',
                '1' => 'A pesar de iniciar, perderías el acceso a:'
            ],

            [
                'original' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '15' => 'Incluso habiendo aprobado, perderías el acceso al certificado internacional:',
                '7' => 'Aprobación no garantiza acceso al certificado internacional:',
                '4' => 'A pesar de haber aprobado, perderías el acceso al certificado internacional:',
                '1' => 'A pesar de aprobar, perderías el acceso al certificado internacional:'
            ],

            [
                'original' => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '15'      => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '7'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '4'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',
                '1'       => 'Ya que tendrías (  ) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*',

            ],
            [
                'original' => '*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*',
                '15'      => '*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*',
                '7'       => '📌 No dejes que esta oportunidad escape de tus manos. *Responde inmediatamente. Tu futuro está en juego.* 💼🚀',
                '4'       => '🚩 🚩 *Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*',
                '1'       => '🚩 🚩 *¡AÚN ES POSIBLE LOGRAR QUE TE CERTIFIQUES!* No pierdas lo que ya has logrado.',

            ],
            [
                'original' => '📌 Ya que este pago, lo debes realizar antes del:',
                '15'      => '📌 Ya que este pago, lo debes realizar antes del:',
                '7'       => '*Si en dado caso no puedes pagar el ponderado, indícame para buscar opciones juntos.*',
                '4'       => '📌 Ya que este pago, lo debes realizar antes del:',
                '1'       => '⏳ *¡Actúa ya!* Paga HOY con un precio especial el ponderado, ¡no pierdas esta oportunidad! ',

            ],
            [
                'original' => '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*',
                '15'      => '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*',
                '7'       => '⚠️ *Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*',
                '4'       => '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*',
                '1'       => '*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*',

            ],


        ];

        foreach ($str as $key => $value) {
            foreach ($value as $key => $val) {
                if ($key == $diff) {
                    $text = str_replace($value['original'], $value[$diff], $text);
                }
            }
        }

        return $text;
    }

    public static function hasCourseCursando($courses)
    {
        $has_course = [];
        foreach ($courses as $key => $course) {

            if ($course['course_status_original'] == 'CURSANDO' && $course['type'] == 'free') {
                $has_course[] = $course;
            }
        }

        return $has_course;
    }

    public static function hasCoursenoCulminado($courses)
    {

        $has_course = false;
        foreach ($courses as $key => $course) {

            if (
                ($course['course_status_original'] == 'NO CULMINÓ' || $course['course_status_original'] == 'NO CULMINO') &&
                $course['type'] == 'free' &&
                ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
            ) {
                $has_course = true;
            }
        }

        return $has_course;
    }

    public static function hasCourseHabilitar($courses)
    {
        $has_course = false;
        foreach ($courses as $key => $course) {

            if ($course['course_status_original'] == 'POR HABILITAR' && $course['type'] == 'free') {
                $has_course = true;
            }
        }

        return $has_course;
    }

    public static function hasCourseAbandonado($courses)
    {
        $has_course = false;
        foreach ($courses as $key => $course) {

            if (
                ($course['course_status_original'] == 'ABANDONÓ' || $course['course_status_original'] == 'ABANDONO') &&
                $course['type'] == 'free' &&
                ($course['month_days'] != $course['diff_days'] || ($course['start'] == null && $course['end'] == null))
            ) {
                $has_course[] = true;
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
            } else {
                $nivel_basico = $course['nivel_basico']['certifaction_test_original'] == 'REPROBADO' || $course['nivel_basico']['certifaction_test_original'] == 'Intentos Pendientes';
                $nivel_intermedio = $course['nivel_intermedio']['certifaction_test_original'] == 'REPROBADO' || $course['nivel_intermedio']['certifaction_test_original'] == 'Intentos Pendientes';
                $nivel_avanzado = $course['nivel_avanzado']['certifaction_test_original'] == 'REPROBADO' || $course['nivel_avanzado']['certifaction_test_original'] == 'Intentos Pendientes';

                if (
                    ($nivel_basico || $nivel_intermedio || $nivel_avanzado) &&
                    $course['type'] == 'free' &&
                    ($course['month_days'] != $course['diff_days'])
                ) {
                    $has_course[] = $course;
                }
            }
        }
        return $has_course;
    }

    public static function getCourseByStatus($course, $status)
    {

        if ($course['course_status_original'] == $status)
            return true;
        return false;
    }
    public static function getCourseByCertificate($course, $status)
    {
        if (isset($course['certifaction_test_original'])) {
            if (($course['certifaction_test_original'] == $status))
                return true;
        } else {
            $nivel_basico = $course['nivel_basico']['certifaction_test_original'] == $status;
            $nivel_intermedio = $course['nivel_intermedio']['certifaction_test_original'] == $status;
            $nivel_avanzado = $course['nivel_avanzado']['certifaction_test_original'] == $status;

            if ($nivel_basico || $nivel_intermedio || $nivel_avanzado) {
                return true;
            } else {

                return false;
            }
        }
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
}
