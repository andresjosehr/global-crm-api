<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;

/**
 * Clase que contiene la lógica de negocio de los mensajes de los estudiantes
 * Por ejemplo, cuando el estudiante se le envían mensajes para que haga la certificación de los cursos SAP y de obsequios
 *
 * Dependencias:
 * - Resources/views/especial-messages carpeta que contiene los mensajes en formato Blade.
 *
 * Estados de cursos validos: COMPLETA, CURSANDO, NO APLICA, NO CULMINÓ, POR HABILITAR, ¿REPROBADO?, ¿ABANDONADO?, ¿APROBADO?
 *
 *
 */
class StudentMessageService
{
    private $__studentData;
    private static $__showTemplateNameInMessageFlag = false; // si se muestra el nombre del template en el mensaje



    public function __construct($studentData = null)
    {
        $this->__studentData = $studentData;
        $this->__inflateStudentData();
    }

    /**
     * Obtiene el mensaje para el estudiante que está cursando y aún no certifica alguno de los cursos SAP
     * Reglas:
     * - Tiene mayor prioridad si hay un curso con vencimiento mas corto ("último día" vs "30 días antes")
     * @param Carbon $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForSAPCourseCertification($processDate)
    {
        $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
        $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];

        $coursesToNotify = []; // almacena solo los cursos SAP a notificar
        $otherFreeCoursesToNotify = []; // almacena solo los cursos SAP a notificar
        $multipleSapCoursesFlag = false; // si hay mas de un curso SAP a notificar
        $multipleSapCoursesWithPendingAttemptsFlag = false; // si hay mas de un curso SAP a notificar con intentos pendientes
        $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar
        $certificationPendingAttemptsFlag = false; // si el estudiante aún posee intentos gratis para certificar
        $noFreeCertificationAttemptsFlag = false; // si el estudiante ya no posee intentos gratis para certificar
        $showOtherSapCoursesFlag = false; // si se muestra la seccion de cursos anteriores de SAP
        $showFreeCoursesFlag = false; // si se muestra la seccion de cursos de obsequio
        $showWarningSapCourseCertificationFlag = false; // si se muestra la seccion de advertencia de certificacion de cursos SAP

        $showNoticeOlderSapCourses = false;
        $showNoticeDisapprovedOlderSapCourses = false;
        $showNoticeDroppedOlderSapCourses = false;
        $showNoticeUnfinishedOlderSapCourses  = false;
        $showNoticeApprovedOlderSapCourses = false;
        $noticeDisapprovedSapCourseNames = false;
        $noticeDroppedSapCourseNames = false;
        $noticeUnfinishedSapCoursesNames = false;
        $noticeApprovedSapCourseNames = false;

        /* Precondiciones:
        - hay un curso SAP con vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
        - el estado del Examen de ese curso es "Sin intentos Gratis" o "Intentos pendientes"
         */
        $studentData = $this->__studentData;
        if (isset($studentData['courses']) == false) :
            throw new \Exception('Error en el formato de datos: no contiene "courses"');
        endif;

        $tmpMultipleSapCoursesWithPendingAttemptsCount = 0;

        foreach ($studentData['courses'] as $course) :
            // si no es curso SAP, sigue procesando el siguiente curso
            if (strpos($course['name'], 'SAP') === false) {
                continue;
            }
            // si el estado del examen es distinto a "Sin intentos Gratis" o "X Intentos pendientes", sigue procesando el siguiente curso
            if (isset($course['certifaction_test_original']) == false) {
                continue;
            }
            $tmpCertificationPendingAttemptsFlag = $course['hasPendingAttempts'];
            $tmpNoFreeCertificationAttemptsFlag = $course['noFreeAttempts'];
            if ($course['hasPendingAttempts'] === false && $tmpNoFreeCertificationAttemptsFlag === false) {
                continue;
            }
            // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
            if (isset($course['end']) == false) {
                continue;
            }
            // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
            $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
            // var_dump($tmpEndCourseDaysAhead);
            if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                continue;
            }
            // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

            // agrega el curso a procesar
            $coursesToNotify[] = $course;
            $endCourseDaysAhead = $tmpEndCourseDaysAhead;
            if ($tmpCertificationPendingAttemptsFlag !== false) {
                $certificationPendingAttemptsFlag = true;
                $tmpMultipleSapCoursesWithPendingAttemptsCount++;
            }
            if ($tmpNoFreeCertificationAttemptsFlag !== false) {
                $noFreeCertificationAttemptsFlag = true;
            }
        endforeach;


        // chequeo si no hay cursos por notificar
        if (count($coursesToNotify) == 0) :
            return null;
        endif;
        $endCourseDate = Carbon::parse($coursesToNotify[0]['end']);

        $sapCoursesNames = [];
        foreach ($coursesToNotify as $course) :
            $sapCoursesNames[] = $course['name'];
        endforeach;

        // flags para múltiples cursos SAP y multiples cursos SAP con intentos pendientes
        if (count($coursesToNotify) > 1) :
            $multipleSapCoursesFlag = true;
            if ($tmpMultipleSapCoursesWithPendingAttemptsCount > 1) :
                $multipleSapCoursesWithPendingAttemptsFlag = true;
            endif;
        endif;

        // //Log::('StudentMessageService::' . __FUNCTION__ . ': $sapCourses', $coursesToNotify);
        // //Log::('StudentMessageService::' . __FUNCTION__ . ': $certificationPendingAttemptsFlag: ' . $certificationPendingAttemptsFlag);
        // //Log::('StudentMessageService::' . __FUNCTION__ . ': $noFreeCertificationAttemptsFlag: ' . $noFreeCertificationAttemptsFlag);


        // Flags para Cursos SAP del pasado que aprobó, reprobó, abandonó o no culminó
        $groupedCourses = $this->__groupCourses($coursesToNotify);
        $sapCourses = $groupedCourses["sapCourses"];
        $otherSapCourses = $groupedCourses["otherSapCourses"];
        $otherFreeCourses = $groupedCourses["otherFreeCourses"];
        $showOtherSapCoursesFlag = (count($otherSapCourses) > 0) ? true : false;

        // Flags para los cursos de obsequio
        $freeCoursesStatuses = self::__getFreeCoursesStatuses($studentData['courses']);
        // //Log::('StudentMessageService::' . __FUNCTION__ . ': $freeCoursesStatuses', $freeCoursesStatuses);
        foreach ($otherSapCourses as $course) :
            if (in_array($course['course_status'], $irregularCourseStatuses)) { // OJO el estado a verificar es del curso SAP, no del curso de obsequio
                $showFreeCoursesFlag = true;

                $showWarningSapCourseCertificationFlag = true; // tweak: si hay cursos irregulares de SAP, se muestra la seccion de advertencia de certificacion de cursos SAP
                break;
            }
        endforeach;

        // Flags para la seccion de advertencia de certificacion de cursos SAP
        if ($showWarningSapCourseCertificationFlag == false) :
            foreach ($freeCoursesStatuses as $course) :
                if (in_array($course['course_status'], $irregularCourseStatuses)) {
                    $showWarningSapCourseCertificationFlag = true;
                    break;
                }
            endforeach;
        endif;

        // Flag Especial si tiene curso(s) reprobado(s) y otro(s) aprobado, y no tiene curso SAP sin habilitar
        $disapprovedSapCourseNames = [];
        $approvedSapCoursesNames = [];
        $toEnableSapCourseNames = [];
        $droppedSapCourseNames = [];
        $unfinishedSapCourseNames = [];
        $pendingSapCoursesNames = []; // se asume q los cursos pendientes son cursos SAP "REPROBADO", "ABANDONADO" o "NO CULMINÓ"
        foreach ($otherSapCourses as $course) :
            if ($course['course_status'] == 'REPROBADO') :
                $disapprovedSapCourseNames[] = $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'APROBADO') :
                $approvedSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'POR HABILITAR') :
                $toEnableSapCourseNames[] =  $course['name'];
            elseif ($course['course_status'] == 'ABANDONADO') :
                $droppedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'NO CULMINÓ') :
                $unfinishedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            // elseif ($course['course_status'] == 'PENDIENTE') :
            //     $pendingSapCoursesNames[] =  $course['name'];
            endif;
        endforeach;

        // agrega a los cursos pendientes de SAP el curso actual
        $pendingSapCoursesNames = array_merge($pendingSapCoursesNames, $sapCoursesNames);

        // si es el ultimo día, y solo tiene un curso SAP
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                $multipleSapCoursesFlag == false // si es 1 curso SAP)
            )
        ) :
            $showNoticeOlderSapCourses = true;
        endif;
        // si es el ultimo día, y tiene curso SAP reprobados y no tiene para Habilitar
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($disapprovedSapCourseNames) > 0 // si tiene cursos SAP reprobados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeDisapprovedOlderSapCourses = true;
            $noticeDisapprovedSapCourseNames = implode(', ', $disapprovedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP abandonados y no tiene para Habilitar
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($droppedSapCourseNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeDroppedOlderSapCourses = true;
            $noticeDroppedSapCourseNames = implode(', ', $droppedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP no culminados y no tiene para Habilitar
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($unfinishedSapCourseNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeUnfinishedOlderSapCourses = true;
            $noticeUnfinishedSapCoursesNames = implode(', ', $unfinishedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP aprobados y no tiene para Habilitar
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($approvedSapCoursesNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeApprovedOlderSapCourses = true;
            $noticeApprovedSapCourseNames = implode(', ', $approvedSapCoursesNames);
        endif;

        // Flag de multiples cursos gratis para habilitar
        $toEnableFreeCoursesCount = 0;
        foreach ($freeCoursesStatuses as $course) :
            if ($course['course_status'] == 'POR HABILITAR') :
                $toEnableFreeCoursesCount++;
            endif;
        endforeach;

        // Fechas de cursos para habilitar
        $toEnableFreeCoursesDates = [
            self::addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 6), // agrega 6 dias habiles a la fecha de proceso
        ];

        // Fechas de cursos para habilitar
        $toEnableSapCoursesDates = [
            self::addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 7), // agrega 7 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 15), // agrega 15 dias habiles a la fecha de proceso
        ];

        // Armado del Template
        $templateFilename =  sprintf(
            "especial-messages.sap-pending-certifications.%d-dias-%s",
            $endCourseDaysAhead,
            self::__getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag)
        );
        // //Log::(sprintf('%s::%s: Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

        $s = [
            'student_name' =>  $this->__studentData['NOMBRE'],
            'studentData' => $this->__studentData,
            'coursesToNotify' => $coursesToNotify,
            'sapCoursesNames' => $sapCoursesNames,
            'sapCourses' => $sapCourses,
            'multipleSapCoursesWithPendingAttemptsFlag' => $multipleSapCoursesWithPendingAttemptsFlag,
            'certificationPendingAttemptsFlag' => $certificationPendingAttemptsFlag,
            'noFreeCertificationAttemptsFlag' => $noFreeCertificationAttemptsFlag,
            'showOlderSapCoursesFlag' => $showOtherSapCoursesFlag,
            'otherSapCourses' => $otherSapCourses,
            'otherFreeCourses' => $otherFreeCourses,
            'showFreeCoursesFlag' => $showFreeCoursesFlag,
            'freeCourses' => $freeCoursesStatuses,
            'showWarningSapCourseCertificationFlag' => $showWarningSapCourseCertificationFlag,
            'endCourseDate' =>  $endCourseDate,
            'showNoticeOlderSapCourses' => $showNoticeOlderSapCourses,
            'showNoticeDisapprovedOlderSapCourses' => $showNoticeDisapprovedOlderSapCourses,
            'showNoticeDroppedOlderSapCourses' => $showNoticeDroppedOlderSapCourses,
            'showNoticeUnfinishedOlderSapCourses' => $showNoticeUnfinishedOlderSapCourses,
            'showNoticeApprovedOlderSapCourses' => $showNoticeApprovedOlderSapCourses,
            'noticeDisapprovedSapCourseNames' => $noticeDisapprovedSapCourseNames,
            'noticeDroppedSapCourseNames' => $noticeDroppedSapCourseNames,
            'noticeUnfinishedSapCoursesNames' => $noticeUnfinishedSapCoursesNames,
            'noticeApprovedSapCourseNames' => $noticeApprovedSapCourseNames,
            'pendingSapCoursesNames' => $pendingSapCoursesNames,
            'toEnableFreeCoursesCount' => $toEnableFreeCoursesCount,
            'toEnableFreeCoursesDates' => $toEnableFreeCoursesDates,
            'toEnableSapCoursesDates' => $toEnableSapCoursesDates,
        ];

        $message = self::__buildMessage($templateFilename, $s);
        //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));

        return $message;
    }


    /**
     * Obtiene el mensaje para el estudiante que está cursando y aún no certifica alguno de los cursos SAP y/o de obsequios
     * Reglas:
     * - Tiene mayor prioridad si hay un curso con vencimiento mas corto ("último día" vs "30 días antes")
     * @param array $studentData Datos del estudiante en formato de array
     * @param Carbon $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForSAPAndFreeCourseCertification($processDate)
    {

        $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
        $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];

        $sapCourses = []; // almacena solo los cursos SAP a notificar
        $freeCourses = []; // almacena solo los cursos de obsequio a notificar
        $otherSapCourses = []; // almacena solo los cursos SAP a notificar
        $otherFreeCourses = []; // almacena solo los cursos Obsequios a notificar
        $multipleSapCoursesFlag = false; // si hay mas de un curso SAP a notificar
        $multipleSapCoursesWithPendingAttemptsFlag = false; // si hay mas de un curso SAP a notificar con intentos pendientes
        $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar
        $certificationPendingAttemptsFlag = false; // si el estudiante aún posee intentos gratis para certificar
        $noFreeCertificationAttemptsFlag = false; // si el estudiante ya no posee intentos gratis para certificar
        $showOlderSapCoursesFlag = false; // si se muestra la seccion de cursos anteriores de SAP
        $showOtherFreeCoursesFlag = false; // si se muestra la seccion de OTROS cursos de obsequio
        $showWarningSapCourseCertificationFlag = false; // si se muestra la seccion de advertencia de certificacion de cursos SAP

        $showNoticeOlderSapCourses = false;

        /* Precondiciones:
        - hay un curso SAP con vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
        - el estado del Examen de ese curso es "Sin intentos Gratis" o "Intentos pendientes"
         */
        $studentData = $this->__studentData;
        if (isset($studentData['courses']) == false) :
            throw new \Exception('Error en el formato de datos: no contiene "courses"');
        endif;

        $tmpMultipleSapCoursesWithPendingAttemptsCount = 0;
        $tmpEndCourseDate = null;

        foreach ($studentData['courses'] as $course) :
            //Log::(sprintf("Curso %s - comienza procesamiento", $course['name']));
            // si no es curso SAP, o el curso no es gratuito, sigue procesando el siguiente curso
            if ($course["isSapCourse"] == false && $course["isFreeCourse"] == false) {
                continue;
            }
            //Log::(sprintf("Curso %s - es un curso SAP o gratis", $course['name']));
            // si el estado del examen es distinto a "Sin intentos Gratis" o "X Intentos pendientes", sigue procesando el siguiente curso

            $tmpCertificationPendingAttemptsFlag = $course['hasPendingAttempts'];
            $tmpNoFreeCertificationAttemptsFlag = $course['noFreeAttempts'];
            if ($tmpCertificationPendingAttemptsFlag === false && $tmpNoFreeCertificationAttemptsFlag === false) {
                continue;
            }
            //Log::(sprintf("Curso %s - tiene intentos pendientes o sin intentos gratis", $course['name']));
            // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
            if (isset($course['end']) == false) {
                continue;
            }
            // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
            $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
            //Log::(sprintf("Curso %s - dias de diferencia %d", $course['name'], $tmpEndCourseDaysAhead));
            // var_dump($tmpEndCourseDaysAhead);
            //Log::('StudentMessageService::' . __FUNCTION__ . ': $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
            if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                continue;
            }
            //Log::(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
            // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

            // agrega el curso a procesar
            if ($course["isSapCourse"] == true) :
                $sapCourses[] = $course;
            else :
                $freeCourses[] = $course;
            endif;

            $endCourseDaysAhead = $tmpEndCourseDaysAhead;
            $tmpEndCourseDate = $course['end'];
            if ($tmpCertificationPendingAttemptsFlag !== false) {
                $certificationPendingAttemptsFlag = true;
                $tmpMultipleSapCoursesWithPendingAttemptsCount++;
            }
            if ($tmpNoFreeCertificationAttemptsFlag !== false) {
                $noFreeCertificationAttemptsFlag = true;
            }
        endforeach;


        // chequeo si no hay cursos por notificar
        if (count($sapCourses) == 0 || count($freeCourses) == 0) :
            // //Log::(sprintf("llega aca: %d - %d ", count($sapCourses), count($freeCourses)));
            return null;
        endif;

        $endCourseDate = Carbon::parse($tmpEndCourseDate);
        //Log::(sprintf("endCourseDate: %s => %s", $tmpEndCourseDate, $endCourseDate->format('d/m/Y')));
        $coursesToNotify = array_merge($sapCourses, $freeCourses);

        // Averigua el nivel de Excel sin intentos gratis
        $excelLevelWithoutFreeCertificationAttempts = null;
        $excelCourseFlag = false;
        foreach ($freeCourses as $course) :
            //Log::('StudentMessageService::' . __FUNCTION__ . ': $course ' . __LINE__ . " " . serialize($course));
            if (stripos($course['name'], 'Excel') !== false) :
                $excelCourseFlag = true;

                if (array_key_exists('nivel_basico', $course) && strpos($course['nivel_basico']['certifaction_test'], 'Sin intentos Gratis') !== false) :
                    $excelLevelWithoutFreeCertificationAttempts = "nivel básico";
                elseif (array_key_exists('nivel_intermedio', $course) && strpos($course['nivel_intermedio']['certifaction_test'], 'Sin intentos Gratis') !== false) :
                    $excelLevelWithoutFreeCertificationAttempts = "nivel intermedio";
                elseif (array_key_exists('nivel_avanzado', $course) && strpos($course['nivel_avanzado']['certifaction_test'], 'Sin intentos Gratis') !== false) :
                    $excelLevelWithoutFreeCertificationAttempts = "nivel avanzado";
                endif;
            endif;
        endforeach;

        // Flags para Cursos SAP del pasado que aprobó, reprobó, abandonó o no culminó
        $groupedCourses = $this->__groupCourses($coursesToNotify);
        $otherSapCourses = $groupedCourses["otherSapCourses"];
        $otherFreeCourses = $groupedCourses["otherFreeCourses"];
        $showOtherSapCoursesFlag = (count($otherSapCourses) > 0) ? true : false;

        //Log::('StudentMessageService::' . __FUNCTION__ . ': $olderSapCourses', $otherSapCourses);
        $showOlderSapCoursesFlag = (count($otherSapCourses) > 0) ? true : false;

        // Flags para los cursos de obsequio
        foreach ($otherSapCourses as $course) :
            if (in_array($course['course_status'], $irregularCourseStatuses)) { // OJO el estado a verificar es del curso SAP, no del curso de obsequio
                $showOtherFreeCoursesFlag = true;

                // $showWarningSapCourseCertificationFlag = true; // tweak: si hay cursos irregulares de SAP, se muestra la seccion de advertencia de certificacion de cursos SAP
                break;
            }
        endforeach;

        // ***************************** REVISAR DESDE ACA *****************************

        $sapCoursesNames = [];
        foreach ($sapCourses as $course) :
            $sapCoursesNames[] = $course['name'];
        endforeach;

        // flags para múltiples cursos SAP y multiples cursos SAP con intentos pendientes
        if (count($sapCourses) > 1) :
            $multipleSapCoursesFlag = true;
            if ($tmpMultipleSapCoursesWithPendingAttemptsCount > 1) :
                $multipleSapCoursesWithPendingAttemptsFlag = true;
            endif;
        endif;

        //Log::('StudentMessageService::' . __FUNCTION__ . ': $sapCourses', $sapCourses);
        //Log::('StudentMessageService::' . __FUNCTION__ . ': $certificationPendingAttemptsFlag: ' . $certificationPendingAttemptsFlag);
        //Log::('StudentMessageService::' . __FUNCTION__ . ': $noFreeCertificationAttemptsFlag: ' . $noFreeCertificationAttemptsFlag);



        // Flags para la seccion de advertencia de certificacion de cursos SAP
        if ($showWarningSapCourseCertificationFlag == false) :
            // cursos SAP
            foreach ($otherSapCourses as $course) :
                if (in_array($course['course_status'], $irregularCourseStatuses)) {
                    $showWarningSapCourseCertificationFlag = true;
                    break;
                }
            endforeach;
            // cursos de obsequio
            foreach ($otherFreeCourses as $course) :
                if (in_array($course['course_status'], $irregularCourseStatuses)) {
                    $showWarningSapCourseCertificationFlag = true;
                    break;
                }
            endforeach;
        endif;

        // Flag Especial si tiene curso(s) reprobado(s) y otro(s) aprobado, y no tiene curso SAP sin habilitar
        $disapprovedSapCourseNames = [];
        $approvedSapCoursesNames = [];
        $toEnableSapCourseNames = [];
        $droppedSapCourseNames = [];
        $unfinishedSapCourseNames = [];
        $pendingSapCoursesNames = []; // se asume q los cursos pendientes son cursos SAP "REPROBADO", "ABANDONADO" o "NO CULMINÓ"
        foreach ($otherSapCourses as $course) :
            if ($course['course_status'] == 'REPROBADO') :
                $disapprovedSapCourseNames[] = $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'APROBADO') :
                $approvedSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'POR HABILITAR') :
                $toEnableSapCourseNames[] =  $course['name'];
            elseif ($course['course_status'] == 'ABANDONADO') :
                $droppedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['course_status'] == 'NO CULMINÓ') :
                $unfinishedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            // elseif ($course['course_status'] == 'PENDIENTE') :
            //     $pendingSapCoursesNames[] =  $course['name'];
            endif;
        endforeach;

        // agrega a los cursos pendientes de SAP el curso actual
        $pendingSapCoursesNames = array_merge($pendingSapCoursesNames, $sapCoursesNames);

        // si es el ultimo día, y solo tiene un curso SAP
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                $multipleSapCoursesFlag == false // si es 1 curso SAP)
            )
        ) :
            $showNoticeOlderSapCourses = true;
        endif;
        // si es el ultimo día, y tiene curso SAP reprobados y no tiene para Habilitar
        $showNoticeDisapprovedOlderSapCourses = false;
        $noticeDisapprovedSapCourseNames = null;
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($disapprovedSapCourseNames) > 0 // si tiene cursos SAP reprobados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeDisapprovedOlderSapCourses = true;
            $noticeDisapprovedSapCourseNames = implode(', ', $disapprovedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP abandonados y no tiene para Habilitar
        $showNoticeDroppedOlderSapCourses = false;
        $noticeDroppedSapCourseNames = null;
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($droppedSapCourseNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeDroppedOlderSapCourses = true;
            $noticeDroppedSapCourseNames = implode(', ', $droppedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP no culminados y no tiene para Habilitar
        $showNoticeUnfinishedOlderSapCourses = false;
        $noticeUnfinishedSapCoursesNames = null;
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($unfinishedSapCourseNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeUnfinishedOlderSapCourses = true;
            $noticeUnfinishedSapCoursesNames = implode(', ', $unfinishedSapCourseNames);
        endif;
        // si es el ultimo día, y tiene curso SAP aprobados y no tiene para Habilitar
        $showNoticeApprovedOlderSapCourses = false;
        $noticeApprovedSapCourseNames = null;
        //Log::("***** Approved", $approvedSapCoursesNames);
        if (
            ($endCourseDaysAhead == 1) // es el ultimo día
            && (
                count($approvedSapCoursesNames) > 0 // si tiene cursos SAP abandonados
                && count($toEnableSapCourseNames) == 0 // si no tiene cursos SAP para habilitar
            )
        ) :
            $showNoticeApprovedOlderSapCourses = true;
            $noticeApprovedSapCourseNames = implode(', ', $approvedSapCoursesNames);
        endif;

        // Flag de multiples cursos gratis para habilitar
        $toEnableFreeCoursesCount = 0;
        foreach ($otherFreeCourses as $course) :
            if ($course['course_status'] == 'POR HABILITAR') :
                $toEnableFreeCoursesCount++;
            endif;
        endforeach;

        // Flags para "intentos pendientes y sin intentos gratis"
        $pendingCoursesToNotifyNames = [];
        $noFreeAttemptsCoursesToNotifyNames = [];
        $noFreeAttemptsSapCoursesToNotifyCount = 0;
        $noFreeAttemptsFreeCoursesToNotifyCount = 0;

        foreach ($coursesToNotify as $course) :
            if (stripos($course['certifaction_test_original'], 'Intentos pendientes') !== false) :
                $pendingCoursesToNotifyNames[] = $course['name'];
            endif;
            if (stripos($course['certifaction_test_original'], 'Sin intentos Gratis') !== false) :
                $noFreeAttemptsCoursesToNotifyNames[] = $course['name'];
                if (strpos($course['name'], 'SAP') !== false) :
                    $noFreeAttemptsSapCoursesToNotifyCount++;
                elseif ($course['type'] == 'free') :
                    $noFreeAttemptsFreeCoursesToNotifyCount++;
                endif;
            endif;
        endforeach;

        // Fechas de cursos para habilitar
        $toEnableFreeCoursesDates = [
            self::addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 6), // agrega 6 dias habiles a la fecha de proceso
        ];

        // Fechas de cursos para habilitar
        $toEnableSapCoursesDates = [
            self::addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 7), // agrega 7 dias habiles a la fecha de proceso
            self::addBusinessDaysToDate($endCourseDate->copy(), 15), // agrega 15 dias habiles a la fecha de proceso
        ];

        // Armado del Template
        $templateFilename =  sprintf(
            "especial-messages.sap-and-free-pending-certifications.%d-dias-%s",
            $endCourseDaysAhead,
            self::__getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag)
        );
        //Log::(sprintf('%s::%s: Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

        $s = [
            'student_name' => $studentData['NOMBRE'],
            'multipleSapCoursesFlag' => $multipleSapCoursesFlag,
            'sapCourses' => $sapCourses,
            'freeCourses' => $freeCourses,
            'endCourseDate' => $endCourseDate,
            'sapCoursesNames' => $sapCoursesNames,
            'multipleSapCoursesWithPendingAttemptsFlag' => $multipleSapCoursesWithPendingAttemptsFlag,
            // 'certificationPendingAttemptsFlag' => $certificationPendingAttemptsFlag,
            // 'noFreeCertificationAttemptsFlag' => $noFreeCertificationAttemptsFlag,
            'showOlderSapCoursesFlag' => $showOlderSapCoursesFlag,
            'otherSapCourses' => $otherSapCourses,
            'otherFreeCourses' => $otherFreeCourses,
            'showOtherFreeCoursesFlag' => $showOtherFreeCoursesFlag,
            'otherFreeCourses' => $otherFreeCourses,
            'showWarningSapCourseCertificationFlag' => $showWarningSapCourseCertificationFlag,
            // 'endCourseDate' =>  $endCourseDate,
            // 'showNoticeOlderSapCourses' => $showNoticeOlderSapCourses,
            'showNoticeDisapprovedOlderSapCourses' => $showNoticeDisapprovedOlderSapCourses,
            'showNoticeDroppedOlderSapCourses' => $showNoticeDroppedOlderSapCourses,
            'showNoticeUnfinishedOlderSapCourses' => $showNoticeUnfinishedOlderSapCourses,
            'showNoticeApprovedOlderSapCourses' => $showNoticeApprovedOlderSapCourses,
            'noticeDisapprovedSapCourseNames' => $noticeDisapprovedSapCourseNames,
            'noticeDroppedSapCourseNames' => $noticeDroppedSapCourseNames,
            'noticeUnfinishedSapCoursesNames' => $noticeUnfinishedSapCoursesNames,
            'noticeApprovedSapCourseNames' => $noticeApprovedSapCourseNames,
            'pendingSapCoursesNames' => $pendingSapCoursesNames,
            'toEnableFreeCoursesCount' => $toEnableFreeCoursesCount,
            'toEnableFreeCoursesDates' => $toEnableFreeCoursesDates,
            'toEnableSapCoursesDates' => $toEnableSapCoursesDates,
            'coursesToNotify' => $coursesToNotify,
            'excelLevelWithoutFreeCertificationAttempts' => $excelLevelWithoutFreeCertificationAttempts,
            'excelCourseFlag' => $excelCourseFlag,
            'pendingCoursesToNotifyNames' => $pendingCoursesToNotifyNames,
            'noFreeAttemptsCoursesToNotifyNames' => $noFreeAttemptsCoursesToNotifyNames,
            'noFreeAttemptsSapCoursesToNotifyCount' => $noFreeAttemptsSapCoursesToNotifyCount,
            'nofreeAttemptsFreeCoursesToNotifyCount' => $noFreeAttemptsFreeCoursesToNotifyCount,
        ];

        $message = self::__buildMessage($templateFilename, $s);


        //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));
        return $message;
    }

    /**
     * Obtiene el mensaje para el estudiante que está cursando cursos gratuitos
     * @param $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForInProgressFreeCourse($processDate)
    {
        try {

            $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
            $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];


            $coursesToNotify = []; // curso a notificar
            $hasExcelCourseToNotify = false; // si hay cursos de Excel a notificar
            $hasSpecializedCoursesToNotify = false; // si hay cursos de Especialización a notificar
            $otherSapCourses = []; // almacen solo los cursos SAP que no se notifican
            $otherFreeCourses = []; // almacena solo los cursos de obsequio que no se notifican

            $hasIncompleteLessons = false; // si tiene lecciones incompletas del curso

            $showDissaprovedOtherCourses = false; // si se muestra la seccion de cursos reprobados
            $showDroppedOtherCourses = false; // si se muestra la seccion de cursos abandonados
            $showInProgressOtherCourses = false; // si se muestra la seccion de cursos "cursando"
            $showToEnableOtherCourses = false; // si se muestra la seccion de cursos "por habilitar"
            $showUnfinishedOtherCourses = false; // si se muestra la seccion de cursos "no culminados"

            //********* */
            $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar


            /* Precondiciones:
            - hay un curso OBsequio con vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
            - el estado del Examen de ese curso es "Intentos pendientes"
             */
            if (empty($this->__studentData) == true || isset($this->__studentData['courses']) == false) :
                throw new \Exception('Error en el formato de datos: no contiene "courses"');
            endif;

            $tmpEndCourseDate = null;

            foreach ($this->__studentData['courses'] as $course) :
                //Log::(sprintf("Curso %s - comienza procesamiento", $course['name']));
                // si no es curso de obsequio, sigue procesando el siguiente curso
                // o no tiene estados pendientes
                if ($course["isFreeCourse"] == false) {
                    continue;
                }
                if ($course["hasPendingAttempts"] == false) {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso de obsequuio con estado pendiente ", $course['name']));

                // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
                if (empty($course['end']) == true) {
                    continue;
                }
                // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
                $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
                //Log::(sprintf("Curso %s - dias de diferencia %d", $course['name'], $tmpEndCourseDaysAhead));
                // var_dump($tmpEndCourseDaysAhead);
                //Log::('StudentMessageService::getMessageForInProgressFreeCourse: $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
                if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                    continue;
                }
                //Log::(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
                // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

                // agrega el curso a procesar
                $coursesToNotify[] = $course;
                $endCourseDaysAhead = $tmpEndCourseDaysAhead;
                $tmpEndCourseDate = $course['end'];
            endforeach;

            // chequeo si no hay cursos por notificar
            if (count($coursesToNotify) == 0) :
                // //Log::(sprintf("llega aca: %d ", count($coursesToNotify)));
                return null;
            endif;

            $endCourseDate = Carbon::parse($tmpEndCourseDate);

            // otros flags necesarios de los cursos
            foreach ($coursesToNotify as $course) :
                if ($course["isExcelCourse"] == true) :
                    $hasExcelCourseToNotify = true;
                endif;
                if ($course["isSpecializedCourse"] == true) :
                    $hasSpecializedCoursesToNotify = true;
                endif;
                if ($course["isExcelCourse"] == true && $course["hasIncompleteLessons"] == true) :
                    $hasIncompleteLessons = true;
                endif;
                // si el curso NO es excel y tiene menos lecciones completas que el total de lecciones
                if ($course["isExcelCourse"] == false && $course["lessons_completed"] < $course["lessons_count"]) :
                    $hasIncompleteLessons = true;
                // si el curso es excel Y tiene lecciones incompletas
                elseif ($course["isExcelCourse"] == true) :
                    foreach ($course["LEVELS"] as $level) :
                        if ($course[$level]["lessons_completed"] < $course[$level]["lessons_count"]) :
                            $hasIncompleteLessons = true;
                            break;
                        endif;
                    endforeach;
                endif;
            endforeach;

            // agrupa los cursos por tipo
            $groupedCourses = $this->__groupCourses($coursesToNotify);
            $sapCourses = $groupedCourses["sapCourses"];
            $freeCourses = $groupedCourses["freeCourses"];
            $otherSapCourses = $groupedCourses["otherSapCourses"];
            $otherFreeCourses = $groupedCourses["otherFreeCourses"];

            // prepara los flags especiales
            foreach ($otherFreeCourses as $course) :
                switch ($course['course_status']):
                    case 'REPROBADO':
                        $showDissaprovedOtherCourses = true;
                        break;
                    case 'ABANDONADO':
                        $showDroppedOtherCourses = true;
                        break;
                    case 'CURSANDO':
                        $showInProgressOtherCourses = true;
                        break;
                    case 'POR HABILITAR':
                        $showToEnableOtherCourses = true;
                        break;
                    case 'NO CULMINÓ':
                        $showUnfinishedOtherCourses = true;
                        break;
                endswitch;
            endforeach;


            // Armado del Template
            $templateFilename =  sprintf(
                "especial-messages.free-courses-pending.%d-dias-%s",
                $endCourseDaysAhead,
                self::__getTemplateFileNamePartForFlags(true, false) // es la precondicion que tenga "intentos pendientes"
            );
            //Log::(sprintf('%s::%s Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

            $s = [
                'studentData' => $this->__studentData,
                'coursesToNotify' => $coursesToNotify,
                'endCourseDate' => $endCourseDate,
                'hasExcelCourseToNotify' => $hasExcelCourseToNotify,
                'hasSpecializedCoursesToNotify' => $hasSpecializedCoursesToNotify,
                'sapCourses' => $sapCourses,
                'freeCourses' => $freeCourses,
                'otherSapCourses' => $otherSapCourses,
                'otherFreeCourses' => $otherFreeCourses,
                'showDissaprovedOtherCourses' => $showDissaprovedOtherCourses,
                'showDroppedOtherCourses' => $showDroppedOtherCourses,
                'showInProgressOtherCourses' => $showInProgressOtherCourses,
                'showToEnableOtherCourses' => $showToEnableOtherCourses,
                'showUnfinishedOtherCourses' => $showUnfinishedOtherCourses,
                'hasIncompleteLessons' => $hasIncompleteLessons,
            ];

            $message = self::__buildMessage($templateFilename, $s);

            //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));
            return $message;
        } catch (\Exception $e) {
            //Log::('StudentMessageService::getMessageForInProgressFreeCourse: ' . $e->getMessage());
            throw $e;
            // return null;
        }
    }

    /**
     * Obtiene el mensaje para el estudiante que está cursando cursos gratuitos
     * @param $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForCompletedFreeCourse($processDate)
    {
        try {

            $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
            $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];


            $coursesToNotify = []; // curso a notificar
            $hasExcelCourseToNotify = false; // si hay cursos de Excel a notificar
            $hasSpecializedCoursesToNotify = false; // si hay cursos de Especialización a notificar
            $otherSapCourses = []; // almacen solo los cursos SAP que no se notifican
            $otherFreeCourses = []; // almacena solo los cursos de obsequio que no se notifican

            $showDissaprovedOtherCourses = false; // si se muestra la seccion de cursos reprobados
            $showDroppedOtherCourses = false; // si se muestra la seccion de cursos abandonados
            $showInProgressOtherCourses = false; // si se muestra la seccion de cursos "cursando"
            $showToEnableOtherCourses = false; // si se muestra la seccion de cursos "por habilitar"
            $showUnfinishedOtherCourses = false; // si se muestra la seccion de cursos "no culminados"

            //********* */
            $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar


            /* Precondiciones:
            - Es un curso de Obsequio
            - Tiene estado "COMPLETO"
            - y, con algunas de estas 2 condiciones:
            - a) Estado de Examen "APROBADO" y  1 día de la fecha $processDate
            - b) Estado de Examen "Sin intentos gratis" y  vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
             */
            if (empty($this->__studentData) == true || isset($this->__studentData['courses']) == false) :
                throw new \Exception('Error en el formato de datos: no contiene "courses"');
            endif;

            $tmpEndCourseDate = null;

            foreach ($this->__studentData['courses'] as $course) :
                //Log::(sprintf("Curso %s - comienza procesamiento", $course['name']));
                // si no es curso de obsequio, sigue procesando el siguiente curso
                // o no tiene estados pendientes

                if (($course["isFreeCourse"] === false) || ($course["course_status"] != "COMPLETA")) {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso de obsequuio con estado completo ", $course['name']));

                // Solo cursos con ESTADO EXAMEN "APROBADO" o "SIN INTENTOS GRATIS"
                if (!($course["certifaction_test_original"] == "APROBADO" || $course["noFreeAttempts"] == true)) :
                    //   if ($course["certifaction_test_original"] != "APROBADO" || $course["noFreeAttempts"] == false) :
                    continue;
                endif;

                // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
                if (empty($course['end']) == true) {
                    continue;
                }
                // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
                $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
                //Log::(sprintf("Curso %s - dias de diferencia %d (%s)", $course['name'], $tmpEndCourseDaysAhead, $course['end']));
                //Log::('StudentMessageService::' . __FUNCTION__ . ': $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
                // Condicion (b) (condicion (a) incluida por ser 1 dia)
                if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                    continue;
                }
                // Condicion (a) pura: si el examen esta aprobado y es el ultimo dia
                if ($course['certifaction_test_original'] == "APROBADO" && $tmpEndCourseDaysAhead != 1) :
                    continue;
                endif;

                //Log::(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
                // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

                // agrega el curso a procesar
                $coursesToNotify[] = $course;
                $endCourseDaysAhead = $tmpEndCourseDaysAhead;
                $tmpEndCourseDate = $course['end'];
            endforeach;

            // chequeo si no hay cursos por notificar
            if (count($coursesToNotify) == 0) :
                // //Log::(sprintf("llega aca: %d ", count($coursesToNotify)));
                return null;
            endif;

            $endCourseDate = Carbon::parse($tmpEndCourseDate);

            // otros flags necesarios de los cursos
            $tmpNoFreeCertificationAttemptsFlag = false;
            $tmpApprovedSapCourseFlag = false;
            foreach ($coursesToNotify as $course) :
                if ($course["isExcelCourse"] == true) :
                    $hasExcelCourseToNotify = true;
                endif;
                if ($course["isSpecializedCourse"] == true) :
                    $hasSpecializedCoursesToNotify = true;
                endif;
                if ($course['noFreeAttempts'] == true) :
                    $tmpNoFreeCertificationAttemptsFlag = true;
                endif;
                if ($course['certifaction_test_original'] == "APROBADO") :
                    $tmpApprovedSapCourseFlag = true;
                endif;
            endforeach;

            // agrupa los cursos por tipo
            $coursesToNotifyIds = array_column($coursesToNotify, 'course_id');
            foreach ($this->__studentData['courses'] as $course) :
                if (in_array($course['course_id'], $coursesToNotifyIds) == true) :
                    continue;
                endif;
                if ($course["isFreeCourse"] == true) :
                    $otherFreeCourses[] = $course;
                elseif ($course["isSapCourse"] == true) :
                    $otherSapCourses[] = $course;
                endif;
            endforeach;

            // prepara los flags especiales
            foreach ($otherFreeCourses as $course) :
                switch ($course['course_status']):
                    case 'REPROBADO':
                        $showDissaprovedOtherCourses = true;
                        break;
                    case 'ABANDONADO':
                        $showDroppedOtherCourses = true;
                        break;
                    case 'CURSANDO':
                        $showInProgressOtherCourses = true;
                        break;
                    case 'POR HABILITAR':
                        $showToEnableOtherCourses = true;
                        break;
                    case 'NO CULMINÓ':
                        $showUnfinishedOtherCourses = true;
                        break;
                endswitch;
            endforeach;


            // Armado del Template
            $templateFilename =  sprintf(
                "especial-messages.free-courses-completed.%d-dias-%s",
                $endCourseDaysAhead,
                self::__getTemplateFileNamePartForFlags(false, $tmpNoFreeCertificationAttemptsFlag, $tmpApprovedSapCourseFlag) // es la precondicion que tenga "intentos pendientes"
            );
            //Log::(sprintf('%s::%s: Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

            $s = [
                'studentData' => $this->__studentData,
                'coursesToNotify' => $coursesToNotify,
                'endCourseDate' => $endCourseDate,
                'hasExcelCourseToNotify' => $hasExcelCourseToNotify,
                'hasSpecializedCoursesToNotify' => $hasSpecializedCoursesToNotify,
                'otherSapCourses' => $otherSapCourses,
                'otherFreeCourses' => $otherFreeCourses,
                'showDissaprovedOtherCourses' => $showDissaprovedOtherCourses,
                'showDroppedOtherCourses' => $showDroppedOtherCourses,
                'showInProgressOtherCourses' => $showInProgressOtherCourses,
                'showToEnableOtherCourses' => $showToEnableOtherCourses,
                'showUnfinishedOtherCourses' => $showUnfinishedOtherCourses,
            ];

            $message = self::__buildMessage($templateFilename, $s);

            //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));
            return $message;
        } catch (\Exception $e) {
            //Log::('StudentMessageService::getMessageForCompletedFreeCourse: ' . $e->getMessage());
            throw $e;
            // return null;
        }
    }

    /**
     * Obtiene el mensaje para el estudiante que tiene cursos certificados y se acerca la fecha de fin
     * @param $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForCertifiedCourse($processDate)
    {
        try {

            $validDaysAhead = [30, 15, 7, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día

            $coursesToNotify = []; // curso a notificar
            $otherSapCourses = []; // almacen solo los cursos SAP que no se notifican
            $otherFreeCourses = []; // almacena solo los cursos de obsequio que no se notifican

            $pendingOtherFreeCourses = []; // cursos pendientes Filas 16 a 30: Fila 16: si tiene un solo curso de obsequio con estado: CURSANDO, COMPLETA, es decir, que no sean POR HABILITAR.
            $pendingOtherSapCourses = []; // cursos pendientes Filas 16 a 30: Fila 16: si tiene un solo curso de obsequio con estado: CURSANDO, COMPLETA, es decir, que no sean POR HABILITAR.
            $otherFreeCourseInProgressOrCompletedCount = 0; // cantidad de cursos de obsequio en estado "cursando" o "completo"
            $show6CoursesOffer = false; // si se muestra la oferta de 6 cursos
            $showOtherFreeCourseOffer = false; // si se muestra la oferta de otro curso de obsequio
            $showSecondChanceOtherFreeCourseOffer = false; // si se muestra la oferta de SEGUNDA CHANCE de otro curso de obsequio

            //********* */
            $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar


            /* Precondiciones:
            - Es un curso de Obsequio o SAP
            - Tiene estado "CERTIFICADO"
            - Con fecha de fin a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
             */
            if (empty($this->__studentData) == true || isset($this->__studentData['courses']) == false) :
                throw new \Exception('Error en el formato de datos: no contiene "courses"');
            endif;

            $tmpEndCourseDate = null;

            foreach ($this->__studentData['courses'] as $course) :
                //Log::(sprintf("Curso %s - comienza procesamiento", $course['name']));
                // si es curso SAP o gratis
                if ($course["isFreeCourse"] == false && $course["isSapCourse"] == false) {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso de obsequio o de SAP ", $course['name']));

                // si no tiene estado CERTIFICADO, sigue procesando otro curso
                if ($course["course_status"] != "CERTIFICADO") {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso certificado ", $course['name']));

                // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
                if (empty($course['end']) == true) {
                    continue;
                }
                // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
                $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
                //Log::(sprintf("Curso %s - dias de diferencia %d", $course['name'], $tmpEndCourseDaysAhead));
                // var_dump($tmpEndCourseDaysAhead);
                //Log::('StudentMessageService::' . __FUNCTION__ . ': $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
                if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                    continue;
                }

                //Log::(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
                // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

                // agrega el curso a procesar
                $coursesToNotify[] = $course;
                $endCourseDaysAhead = $tmpEndCourseDaysAhead;
                $tmpEndCourseDate = $course['end'];
            endforeach;

            // chequeo si no hay cursos por notificar
            if (count($coursesToNotify) == 0) :
                // //Log::(sprintf("llega aca: %d ", count($coursesToNotify)));
                return null;
            endif;

            $endCourseDate = Carbon::parse($tmpEndCourseDate);

            // agrupa los cursos por tipo
            $groupedCourses = $this->__groupCourses($coursesToNotify);
            $sapCourses = $groupedCourses["sapCourses"];
            $otherSapCourses = $groupedCourses["otherSapCourses"];
            $otherFreeCourses = $groupedCourses["otherFreeCourses"];

            // determina $pendingOtherFreeCourses
            foreach ($otherFreeCourses as $course) :
                if ($course['course_status'] == 'POR HABILITAR' || $course['course_status'] == 'CURSANDO' || $course['course_status'] == 'COMPLETA') :
                    $pendingOtherFreeCourses[] = $course;
                endif;
            endforeach;

            // prepara los flags especiales
            foreach ($otherFreeCourses as $course) :
                switch ($course['course_status']):
                    case 'NO APLICA':
                        $showOtherFreeCourseOffer = true;
                        break;
                    case 'CURSANDO':
                        $otherFreeCourseInProgressOrCompletedCount++;
                        break;
                    case 'COMPLETADO':
                        $otherFreeCourseInProgressOrCompletedCount++;
                        break;
                endswitch;
                // si en las columnas de CERTIFICADO tiene el estado "NO APLICA", muestra la oferta de segunda chanc                e
                //Log::("curso " . $course['name']);
                if ($course['certifaction_test_original'] == 'NO APLICA') :
                    $showSecondChanceOtherFreeCourseOffer = true;
                endif;
            endforeach;

            // flag especial de 6 cursos en la columna SAP
            // Filas 32 y 33: solo si en la columna SAP, tiene menos de 6 cursos
            $show6CoursesOffer = (count(explode("+", $this->__studentData["SAP"])) < 6);


            // Armado del Template: {dias}-{sap/free}-{con/sin}cursos pendientes
            $templateFilename =  sprintf(
                "especial-messages.certified-courses.%s-dias-%s-%s_cursos_pendientes",
                (($endCourseDaysAhead == 1) ? "1" : "30_15_7_4"),
                (($coursesToNotify[0]["isSapCourse"] == true) ? "sap" : "free"),
                ((count($pendingOtherFreeCourses) > 0) ? "con" : "sin")
            );
            //Log::(sprintf('%s::%s: Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

            $s = [
                'studentData' => $this->__studentData,
                'coursesToNotify' => $coursesToNotify,
                'endCourseDate' => $endCourseDate,

                'otherSapCourses' => $otherSapCourses,
                'otherFreeCourses' => $otherFreeCourses,

                'pendingOtherFreeCourses' => $pendingOtherFreeCourses,
                'otherFreeCourseInProgressOrCompletedCount' => $otherFreeCourseInProgressOrCompletedCount,
                'show6CoursesOffer' => $show6CoursesOffer,
                'showOtherFreeCourseOffer' => $showOtherFreeCourseOffer,
                'showSecondChanceOtherFreeCourseOffer' => $showSecondChanceOtherFreeCourseOffer,

            ];

            $message = self::__buildMessage($templateFilename, $s);

            //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));
            return $message;
        } catch (\Exception $e) {
            //Log::('StudentMessageService::getMessageForInProgressFreeCourse: ' . $e->getMessage());
            throw $e;
            // return null;
        }
    }

    /**
     * Obtiene el mensaje para el estudiante que hizo extension del curso
     * @param $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForExtension($processDate)
    {
        try {

            $validDaysAhead = [30]; // días de adelanto: pueden 30, 15, 7, 4 y 3 días

            $coursesToNotify = []; // curso a notificar

            //********* */
            $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar


            /* Precondiciones:
            - Es un curso de Obsequio o SAP
            - Tiene estado "CURSANDO"
            - Estudiante tiene "EXTENSION" distinto de cadena vacia
            - Con fecha de fin a los 30 días, 15 días, 7 días, 4 días y 3 día de la fecha $processDate
             */
            if (empty($this->__studentData) == true || isset($this->__studentData['courses']) == false) :
                throw new \Exception('Error en el formato de datos: no contiene "courses"');
            endif;

            $tmpEndCourseDate = null;

            foreach ($this->__studentData['courses'] as $course) :
                //Log::(sprintf("Curso %s - comienza procesamiento", $course['name']));
                // si es curso SAP o gratis
                if ($course["isFreeCourse"] == false && $course["isSapCourse"] == false) {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso de obsequio o de SAP ", $course['name']));

                // si no tiene estado CERTIFICADO, sigue procesando otro curso
                if ($course["course_status"] != "CURSANDO") {
                    continue;
                }
                //Log::(sprintf("Curso %s - es un curso que esta cursando ", $course['name']));

                // si no tiene estado CERTIFICADO, sigue procesando otro curso
                $tmpExtension = trim($this->__studentData["EXTENSION"]);
                if ($tmpExtension == "") {
                    continue;
                }
                //Log::(sprintf("Curso %s - el alumno tiene extension", $course['name']));

                // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
                if (empty($course['end']) == true) {
                    continue;
                }
                // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
                $tmpEndCourseDaysAhead = $this->__calculateDayDifference($processDate, Carbon::parse($course['end']));
                //Log::(sprintf("Curso %s - dias de diferencia %d", $course['name'], $tmpEndCourseDaysAhead));
                // var_dump($tmpEndCourseDaysAhead);
                //Log::('StudentMessageService::' . __FUNCTION__ . ': $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
                if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                    continue;
                }

                //Log::(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
                // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

                // agrega el curso a procesar
                $coursesToNotify[] = $course;
                $endCourseDaysAhead = $tmpEndCourseDaysAhead;
                $tmpEndCourseDate = $course['end'];
            endforeach;

            // chequeo si no hay cursos por notificar
            if (count($coursesToNotify) == 0) :
                // //Log::(sprintf("llega aca: %d ", count($coursesToNotify)));
                return null;
            endif;

            $endCourseDate = Carbon::parse($tmpEndCourseDate);

            // Armado del Template: {dias}-{sap/free}-{con/sin}cursos pendientes
            $templateFilename =  sprintf(
                "especial-messages.extension.30_15_7_4_3-dias"
            );
            //Log::(sprintf('%s::%s: Template %s: ', __CLASS__, __FUNCTION__, $templateFilename));

            $s = [
                'studentData' => $this->__studentData,
                'coursesToNotify' => $coursesToNotify,
                'endCourseDate' => $endCourseDate,

            ];

            $message = self::__buildMessage($templateFilename, $s);

            //Log::(sprintf('%s::%s: Message %s: ', __CLASS__, __FUNCTION__, $message));

            return $message;
        } catch (\Exception $e) {
            //Log::('StudentMessageService::getMessageForInProgressFreeCourse: ' . $e->getMessage());
            throw $e;
            // return null;
        }
    }

    /**
     * Obtiene el mensaje para el estudiante
     * @param $templateFilename Nombre del archivo de template
     * @param $vars Variables para el template
     */
    private static function __buildMessage($templateFilename, $vars)
    {

        $message = view($templateFilename, $vars)->render();
        // elimina espacios en blanco al inicio de la linea, que se usan para jerarquias de programación
        $message = preg_replace('/^[ ]+/m', '', $message);

        // $message = preg_replace("/\n\n+/", "\n\n", $message);
        // $message = preg_replace("/\r\n\r\n+/", "\r\n\r\n", $message);
        $message = preg_replace('/(\R){2,}/', "\r\n\r\n", $message);


        // @todo eliminar esta linea - es solo para debug visual
        if (self::$__showTemplateNameInMessageFlag == true) :
            $message .= "

-- plantilla: $templateFilename --
        ";
        endif;

        return $message;
    }

    /**
     * Obtiene el estado de cursos viejos de SAP
     * @param array $courses Datos de los cursos del estudiante
     * @return array|null Retorna un array con los datos del curso viejo de SAP (name, status) o null si no hay cursos viejos de SAP
     */
    private static function __getOlderSapCoursesStatuses($courses)
    {
        $validCourseStatus = ['APROBADO', 'REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];
        $olderSapCoursesStatuses = [];
        foreach ($courses as $course) :
            // si no es curso SAP, sigue procesando el siguiente curso
            if (strpos($course['name'], 'SAP') === false) {
                continue;
            }
            // si no es un estado valido de curso, sigue procesando el siguiente curso
            if (in_array($course['course_status'], $validCourseStatus) == false) {
                continue;
            }

            $olderSapCoursesStatuses[] =
                [
                    'name' => $course['name'],
                    'course_status' => $course['course_status'],
                    'statusToDisplay' => self::courseStatusToDisplay($course['course_status']),
                ];
        endforeach;
        return $olderSapCoursesStatuses;
    }

    /**
     * Obtiene el estado de cursos viejos de SAP
     * @param array $courses Datos de los cursos del estudiante
     * @return array|null Retorna un array con los datos del curso viejo de SAP (name, status) o null si no hay cursos viejos de SAP
     */
    private static function __getFreeCoursesStatuses($courses)
    {
        $validCourseStatus = ['CURSANDO', 'APROBADO', 'REPROBADO', 'ABANDONADO', 'NO CULMINÓ', 'POR HABILITAR'];
        $freeCoursesStatuses = [];
        foreach ($courses as $course) :
            // si no es curso SAP, sigue procesando el siguiente curso
            if ($course['type'] != "free") {
                continue;
            }
            // si no es un estado valido de curso, sigue procesando el siguiente curso
            if (in_array($course['course_status'], $validCourseStatus) == false) {
                continue;
            }

            $freeCoursesStatuses[] =
                [
                    'name' => $course['name'],
                    'course_status' => $course['course_status'],
                    'statusToDisplay' => self::courseStatusToDisplay($course['course_status']),
                ];
        endforeach;
        return $freeCoursesStatuses;
    }

    /**
     * Obtiene parte del nombre del archivo de template basado en los flags de certificacion de cursos
     */
    private static function __getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag, $approvedCertificationFlag = false)
    {
        if ($certificationPendingAttemptsFlag == true && $noFreeCertificationAttemptsFlag == false) :
            // Si no hay intentos pendientes ni intentos gratis, se muestra el mensaje de "certificacion de cursos SAP"
            return 'con_intentos_pendientes';
        elseif ($certificationPendingAttemptsFlag == false && $noFreeCertificationAttemptsFlag == true) :
            // Si hay intentos pendientes pero no hay intentos gratis, se muestra el mensaje de "certificacion de cursos SAP"
            return          'sin_intentos_gratis';
        elseif ($certificationPendingAttemptsFlag == true && $noFreeCertificationAttemptsFlag == true) :
            // Si no hay intentos pendientes pero hay intentos gratis, se muestra el mensaje de "certificacion de cursos SAP"
            return  'con_intentos_pendientes-y-sin_intentos_gratis';
        elseif ($approvedCertificationFlag == true) :
            return "aprobado";
        else :
            return "";
        endif;
    }

    /**
     * Obtiene el estado de un curso para mostrar en el mensaje
     * @param string $courseStatus Estado del curso
     * @return string Retorna el estado del curso en formato de texto
     */
    public static function courseStatusToDisplay($courseStatus)
    {
        $courseStatuses = [
            'COMPLETA' => 'completaste',
            'CURSANDO' => 'estás cursando',
            'NO APLICA' => 'no aplica',
            'NO CULMINÓ' => 'no culminaste',
            'POR HABILITAR' => 'Por habilitar',
            'REPROBADO' => 'reprobaste',
            'ABANDONADO' => 'abandonaste',
            'APROBADO' => 'aprobaste',
        ];
        return $courseStatuses[$courseStatus];
    }

    /**
     * Agrega un número de días hábiles a una fecha.
     * Saltea los domingos
     * @param Carbon $baseDate Fecha base
     * @param int $businessDaysToAdd Número de días hábiles a agregar
     */
    public static function addBusinessDaysToDate(Carbon $baseDate, $businessDaysToAdd)
    {
        // Agrega los días especificados a la fecha
        $dateResult = $baseDate->addDays($businessDaysToAdd);

        // recursión hasta encontrar fecha valida
        if (self::isBusinessDay($dateResult) == false) {
            return self::addBusinessDaysToDate($dateResult, 1);
        }

        return $dateResult;
    }

    /**
     * Verifica si una fecha es un día hábil
     * @param Carbon $baseDate Fecha a verificar
     */
    public static function isBusinessDay(Carbon $baseDate)
    {
        // Verifica si la fecha es un domingo => false
        if ($baseDate->dayOfWeek == Carbon::SUNDAY) {
            return false;
        }

        // Verifica si la fecha resultante es feriado
        if (in_array($baseDate->toDateString(), self::getHolidays())) {
            return false;
        }

        // pasó los filtros
        return true;
    }

    /**
     * Lee los feriados
     */
    public static function getHolidays()
    {
        $holidays = config('globalcrm.holidays');
        return $holidays;
    }


    /**
     * Agrega flags al array de $studentData para cachear ciertos procesos
     * - isSapCourse, isFreeCourse, isExcelCourse, isSpecializedCourse
     * - hasPendingAttempts, noFreeAttempts
     */
    private function __inflateStudentData()
    {

        // Claves que deben existir en el array de $studentData para que no falle la vista Blade
        // LEVELS es para almacenar niveles de Excel
        // APPROVED_LEVELS_COUNT es para almacenar la cantidad de niveles de Excel aprobados
        $requiredKeys = ["AULA SAP", "EXAMEN", "CERTIFICADO", "NOMBRE", "PONDERADO SAP", "LEVELS", "APPROVED_LEVELS_COUNT"];

        // Verificar y establecer las claves si no existen
        foreach ($requiredKeys as $key) :
            if (!array_key_exists($key, $this->__studentData)) :
                $this->__studentData[$key] = "";
            endif;
        endforeach;

        $this->__studentData['NOMBRE'] = trim($this->__studentData['NOMBRE COMPLETO CLIENTE']);

        // Claves que deben existir en el array de $course para que no falle la vista Blade
        if (isset($this->__studentData['courses']) == false) :
            $this->__studentData['courses'] = [];
            return;
        endif;

        foreach ($this->__studentData['courses'] as &$course) :
            $course['isSapCourse'] = (strpos($course['name'], 'SAP') !== false) ? true : false;
            $course['isFreeCourse'] = ($course['type'] == 'free') ? true : false;
            if (isset($course["certifaction_test_original"]) == false) :
                $course["certifaction_test_original"] = "";
            endif;
            if (isset($course["LEVELS"]) == false) :
                $course["LEVELS"] = [];
            endif;
            if (isset($course["APPROVED_LEVELS_COUNT"]) == false) :
                $course["APPROVED_LEVELS_COUNT"] = 0;
            endif;
            // los cursos de Excel son gratis
            $course['isExcelCourse'] = (($course['isFreeCourse'] == true) && (strpos($course['name'], 'Excel') !== false));
            // los otros cursos gratuitos que no son de Excel son cursos especializados
            $course['isSpecializedCourse'] = (($course['isFreeCourse'] == true) && ($course['isExcelCourse'] == false));
            // flag para cursos con intentos pendientes, EXCEPTO Excel
            $tmpCertifaction_test_original = strtolower($course["certifaction_test_original"]);
            $course['hasPendingAttempts'] = (($course['isExcelCourse'] == false) && ((stripos($tmpCertifaction_test_original, 'intentos pendientes') || stripos($tmpCertifaction_test_original, 'intento pendiente'))) !== false);
            // flag para cursos sin intentos gratis
            $course['noFreeAttempts'] = (($course['isExcelCourse'] == false) && stripos($tmpCertifaction_test_original, 'sin intentos gratis') !== false);
            // flag de cantidad de intentos pendientes
            if ($course['hasPendingAttempts'] == true) :
                $course['pendingAttemptsCount'] = $this->extractPendingAttempts($course["certifaction_test_original"]);
            else :
                $course['pendingAttemptsCount'] = 0;
            endif;
            // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
            if (isset($course['end']) == false) {
                $course['end'] = null;
            }
            $course['APPROVED_LEVELS_COUNT'] = 0;

            // caso especial, si no existe el "course_status" pero existe el "course_status_original"
            if (isset($course['course_status']) == false && isset($course['course_status_original']) == true) :
                $course['course_status'] = $course['course_status_original'];
            endif;

            // Aplana cursos de Excel con sus flags
            if ($course['isExcelCourse'] == true) :
                $course['LEVELS'] = []; // Excel tiene los niveles
                $course["hasIncompleteLessons"] = false; // flag para cursos con lecciones incompletas
                $tmpLevels = ['nivel_basico', 'nivel_intermedio', 'nivel_avanzado'];
                foreach ($tmpLevels as $level) :
                    // si no existe el nivel, sigue procesando
                    if (isset($course[$level]) == false) :
                        continue;
                    endif;

                    $course['LEVELS'][] = $level; // agrega el nivel al procesamiento
                    $course[$level]['name'] = ucwords(str_replace('_', ' ', $level)); // asigna el nombre del nivel
                    if ($course[$level]['course_status'] == 'APROBADO') :
                        $course['APPROVED_LEVELS_COUNT']++;
                    endif;

                    if (isset($course[$level]['certifaction_test_original']) == true) :
                        $tmpCertifaction_test_original = strtolower($course[$level]['certifaction_test_original']);

                        // flag para NIVELES de cursos con intentos pendientes
                        $course[$level]['hasPendingAttempts'] = (stripos($tmpCertifaction_test_original, 'intentos pendientes') !== false || stripos($tmpCertifaction_test_original, 'intento pendiente') !== false);
                        // flag para cursos sin intentos gratis
                        $course[$level]['noFreeAttempts'] = (stripos($tmpCertifaction_test_original, 'sin intentos gratis') !== false);

                        // flag para el CURSO. "|" el or es por si ya estaba el true antes
                        $course['hasPendingAttempts'] =  ($course['hasPendingAttempts'] || $course[$level]['hasPendingAttempts']);
                        $course['noFreeAttempts'] = ($course['noFreeAttempts'] || $course[$level]['noFreeAttempts']);

                        // cantidad de intentos pendientes
                        if ($course['hasPendingAttempts'] == true) :
                            $course[$level]['pendingAttemptsCount'] = $this->extractPendingAttempts($course[$level]['certifaction_test_original']);
                        else :
                            $course[$level]['pendingAttemptsCount'] = 0;
                        endif;

                    endif;

                    // setea el flag en el CURSO de lecciones incompletas
                    if ($course[$level]["lessons_completed"] < $course[$level]["lessons_count"]) :
                        $course["hasIncompleteLessons"] = true;
                    endif;

                endforeach;
            endif;
        endforeach;
    }

    /**
     * Agrupa los cursos por tipo
     * @param array $coursesToNotify Cursos a notificar
     * @return array Retorna un array con los cursos agrupados por tipo
     * - ["sapCourses"] => [cursos SAP a notificar]
     * - ["freeCourses"] => [cursos de obsequio a notificar]
     * - ["otherSapCourses"] => [cursos SAP que no se notifican]
     * - ["otherFreeCourses"] => [cursos de obsequio que no se notifican]
     */
    private function __groupCourses($coursesToNotify)
    {
        $groupedCourses = [
            "sapCourses" => [],
            "freeCourses" => [],
            "otherSapCourses" => [],
            "otherFreeCourses" => [],
        ];
        // agrupa los cursos por tipo
        $coursesToNotifyIds = array_column($coursesToNotify, 'course_id');
        foreach ($this->__studentData['courses'] as $course) :
            if (in_array($course['course_id'], $coursesToNotifyIds) == true) :
                if ($course["isSapCourse"] == true) :
                    $groupedCourses["sapCourses"][] = $course;
                elseif ($course["isFreeCourse"] == true) :
                    $groupedCourses["freeCourses"][] = $course;
                endif;
            else :
                if ($course["isSapCourse"] == true) :
                    $groupedCourses["otherSapCourses"][] = $course;
                elseif ($course["isFreeCourse"] == true) :
                    $groupedCourses["otherFreeCourses"][] = $course;
                endif;
            endif;
        endforeach;
        return $groupedCourses;
    }

    /**
     * calcula la diferencia en dias entre la fecha de proceso y la fecha de fin del curso
     * @param Carbon $carbonProcessDate Fecha de proceso
     * @param Carbon $carbonCourseEndDate Fecha de fin del curso
     * @return int Retorna la diferencia en dias entre la fecha de proceso y la fecha de fin del curso
     */
    private function __calculateDayDifference($carbonProcessDate, $carbonCourseEndDate)
    {
        $daysAdjustments = [28, 29, 30, 31, 32];
        // Calcular la diferencia en días
        $dayDiff = $carbonProcessDate->diffInDays($carbonCourseEndDate, false) + 1;

        // Sobreescribe para dias especiales, cuando hay 28, 29, 30 o 31 dias de diferencia pero es el mismo dia calendario
        if (($carbonProcessDate->format('j') === $carbonCourseEndDate->format('j')) // mismo dia
            && (in_array($dayDiff, $daysAdjustments)) // un mes de diferencia
        ) :
            $dayDiff = 30;
        endif;

        return $dayDiff;
    }

    public function testDate($progressDate)
    {
        $testDates = [
            "2024-01-10",
            "2024-01-13",
            "2024-01-16",
            "2024-01-24",
            "2024-02-10",
            "2024-01-11",
            "2024-01-09",
            "2024-01-12",
            "2024-01-15",
            "2024-01-18",
            "2024-01-26",
        ];

        foreach ($testDates as $date) :
            $carbonDate = Carbon::parse($date);

            $dayDiff = self::__calculateDayDifference($progressDate, $carbonDate);
            printf("<p>%s - %s - %d</p>", $progressDate->format('Y-m-d'), $carbonDate->format('Y-m-d'), $dayDiff);
        endforeach;
    }

    /**
     * Extrae la cantidad de intentos pendientes
     */
    public function extractPendingAttempts($certifaction_test_original)
    {
        $tmpCertifaction_test_original = trim(strtolower($certifaction_test_original));
        switch ($tmpCertifaction_test_original):
            case 'sin intentos gratis':
                $tmpPendingAttempts = 0;
                break;
            case '1 intento pendiente':
                $tmpPendingAttempts = 1;
                break;
            case '2 intentos pendientes':
                $tmpPendingAttempts = 2;
                break;
            case '3 intentos pendientes':
                $tmpPendingAttempts = 3;
                break;
            default:
                $tmpPendingAttempts = 0;
                break;
        endswitch;
        return $tmpPendingAttempts;
    }
}
