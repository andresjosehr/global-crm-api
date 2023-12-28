<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
    /**
     * Obtiene el mensaje para el estudiante que está cursando y aún no certifica alguno de los cursos SAP
     * Reglas:
     * - Tiene mayor prioridad si hay un curso con vencimiento mas corto ("último día" vs "30 días antes")
     * @param array $studentData Datos del estudiante en formato de array
     * @param Carbon $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForSAPCourseCertification($studentData, $processDate)
    {
        $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
        $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];

        $sapCourses = []; // almacena solo los cursos SAP a notificar
        $multipleSapCoursesFlag = false; // si hay mas de un curso SAP a notificar
        $multipleSapCoursesWithPendingAttemptsFlag = false; // si hay mas de un curso SAP a notificar con intentos pendientes
        $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar
        $certificationPendingAttemptsFlag = false; // si el estudiante aún posee intentos gratis para certificar
        $noFreeCertificationAttemptsFlag = false; // si el estudiante ya no posee intentos gratis para certificar
        $showOlderSapCoursesFlag = false; // si se muestra la seccion de cursos anteriores de SAP
        $showFreeCoursesFlag = false; // si se muestra la seccion de cursos de obsequio
        $showWarningSapCourseCertificationFlag = false; // si se muestra la seccion de advertencia de certificacion de cursos SAP

        /* Precondiciones:
        - hay un curso SAP con vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
        - el estado del Examen de ese curso es "Sin intentos Gratis" o "Intentos pendientes"
         */
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
            $tmpCertificationPendingAttemptsFlag = stripos($course['certification_test_original'], 'Intentos pendientes');
            $tmpNoFreeCertificationAttemptsFlag = stripos($course['certifaction_test'], 'Sin intentos Gratis');
            if ($tmpCertificationPendingAttemptsFlag === false && $tmpNoFreeCertificationAttemptsFlag === false) {
                continue;
            }
            // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
            if (isset($course['end']) == false) {
                continue;
            }
            // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
            $tmpEndCourseDaysAhead = $processDate->diffInDays(Carbon::parse($course['end']));
            // var_dump($tmpEndCourseDaysAhead);
            Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
            if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                continue;
            }
            // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

            // agrega el curso a procesar
            $sapCourses[] = $course;
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
        if (count($sapCourses) == 0) :
            return null;
        endif;

        $endCourseDate = Carbon::parse($sapCourses[0]['end']);

        $tmpSapCoursesNames = [];
        foreach ($sapCourses as $course) :
            $tmpSapCoursesNames[] = $course['name'];
        endforeach;
        $sapCoursesNames = implode(', ', $tmpSapCoursesNames);

        // flags para múltiples cursos SAP y multiples cursos SAP con intentos pendientes
        if (count($sapCourses) > 1) :
            $multipleSapCoursesFlag = true;
            if ($tmpMultipleSapCoursesWithPendingAttemptsCount > 1) :
                $multipleSapCoursesWithPendingAttemptsFlag = true;
            endif;
        endif;

        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $sapCourses', $sapCourses);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $certificationPendingAttemptsFlag: ' . $certificationPendingAttemptsFlag);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $noFreeCertificationAttemptsFlag: ' . $noFreeCertificationAttemptsFlag);


        // Flags para Cursos SAP del pasado que aprobó, reprobó, abandonó o no culminó
        $olderSapCourses = self::__getOlderSapCoursesStatuses($studentData['courses']);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $olderSapCourses', $olderSapCourses);
        $showOlderSapCoursesFlag = (count($olderSapCourses) > 0) ? true : false;

        // Flags para los cursos de obsequio
        $freeCoursesStatuses = self::__getFreeCoursesStatuses($studentData['courses']);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $freeCoursesStatuses', $freeCoursesStatuses);
        foreach ($olderSapCourses as $course) :
            if (in_array($course['status'], $irregularCourseStatuses)) { // OJO el estado a verificar es del curso SAP, no del curso de obsequio
                $showFreeCoursesFlag = true;

                $showWarningSapCourseCertificationFlag = true; // tweak: si hay cursos irregulares de SAP, se muestra la seccion de advertencia de certificacion de cursos SAP
                break;
            }
        endforeach;

        // Flags para la seccion de advertencia de certificacion de cursos SAP
        if ($showWarningSapCourseCertificationFlag == false) :
            foreach ($freeCoursesStatuses as $course) :
                if (in_array($course['status'], $irregularCourseStatuses)) {
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
        foreach ($olderSapCourses as $course) :
            if ($course['status'] == 'REPROBADO') :
                $disapprovedSapCourseNames[] = $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'APROBADO') :
                $approvedSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'POR HABILITAR') :
                $toEnableSapCourseNames[] =  $course['name'];
            elseif ($course['status'] == 'ABANDONADO') :
                $droppedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'NO CULMINÓ') :
                $unfinishedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            // elseif ($course['status'] == 'PENDIENTE') :
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
            if ($course['status'] == 'POR HABILITAR') :
                $toEnableFreeCoursesCount++;
            endif;
        endforeach;

        // Fechas de cursos para habilitar
        $toEnableFreeCoursesDates = [
            self::__addBusinessDaysToDate($endCourseDate, 3), // agrega 3 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate, 6), // agrega 6 dias habiles a la fecha de proceso
        ];

        // Fechas de cursos para habilitar
        $toEnableSapCoursesDates = [
            self::__addBusinessDaysToDate($endCourseDate, 3), // agrega 3 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate, 7), // agrega 7 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate, 15), // agrega 15 dias habiles a la fecha de proceso
        ];

        // Armado del Template      
        $templateFilename =  sprintf(
            "especial-messages.sap-pending-certifications.%d-dias-%s",
            $endCourseDaysAhead,
            self::__getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag)
        );
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $templateFilename: ' . $templateFilename);

        $s = [
            'student_name' => $studentData['NOMBRE'],
            'multipleSapCoursesFlag' => $multipleSapCoursesFlag,
            'sapCourses' => $sapCourses,
            'sapCoursesNames' => $sapCoursesNames,
            'multipleSapCoursesWithPendingAttemptsFlag' => $multipleSapCoursesWithPendingAttemptsFlag,
            'certificationPendingAttemptsFlag' => $certificationPendingAttemptsFlag,
            'noFreeCertificationAttemptsFlag' => $noFreeCertificationAttemptsFlag,
            'showOlderSapCoursesFlag' => $showOlderSapCoursesFlag,
            'olderSapCourses' => $olderSapCourses,
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
        $text = view($templateFilename, $s)->render();
        // remove \n consecutives. Only two \n
        $text = preg_replace("/\n\n+/", "\n\n", $text);

        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $text: ' . $text);

        // Lógica del servicio
        return $text;
    }


    /**
     * Obtiene el mensaje para el estudiante que está cursando y aún no certifica alguno de los cursos SAP y/o de obsequios
     * Reglas:
     * - Tiene mayor prioridad si hay un curso con vencimiento mas corto ("último día" vs "30 días antes")
     * @param array $studentData Datos del estudiante en formato de array
     * @param Carbon $processDate Fecha de procesamiento. Por lo general es la fecha actual ("hoy"). También puede ser "mañana" y "pasado mañana" para procesar los fines de semana y días festivos.
     */
    public function getMessageForSAPAndFreeCourseCertification($studentData, $processDate)
    {
        $validDaysAhead = [30, 15, 7, 4, 1]; // días de adelanto: pueden 30, 15, 7, 4 y 1 día
        $irregularCourseStatuses = ['REPROBADO', 'ABANDONADO', 'NO CULMINÓ'];

        $sapCourses = []; // almacena solo los cursos SAP a notificar
        $freeCourses = []; // almacena solo los cursos de obsequio a notificar
        $multipleSapCoursesFlag = false; // si hay mas de un curso SAP a notificar
        $multipleSapCoursesWithPendingAttemptsFlag = false; // si hay mas de un curso SAP a notificar con intentos pendientes
        $endCourseDaysAhead = 999; // dias de adelanto: pueden 30, 15, 7, 4 y 1 día. 999 significa que no hay cursos SAP a notificar
        $certificationPendingAttemptsFlag = false; // si el estudiante aún posee intentos gratis para certificar
        $noFreeCertificationAttemptsFlag = false; // si el estudiante ya no posee intentos gratis para certificar
        $showOlderSapCoursesFlag = false; // si se muestra la seccion de cursos anteriores de SAP
        $showOtherFreeCoursesFlag = false; // si se muestra la seccion de OTROS cursos de obsequio
        $showWarningSapCourseCertificationFlag = false; // si se muestra la seccion de advertencia de certificacion de cursos SAP


        /* Precondiciones:
        - hay un curso SAP con vencimiento a los 30 días, 15 días, 7 días, 4 días y 1 día de la fecha $processDate
        - el estado del Examen de ese curso es "Sin intentos Gratis" o "Intentos pendientes"
         */
        if (isset($studentData['courses']) == false) :
            throw new \Exception('Error en el formato de datos: no contiene "courses"');
        endif;

        $tmpMultipleSapCoursesWithPendingAttemptsCount = 0;
        $tmpEndCourseDate = null;

        foreach ($studentData['courses'] as $course) :
            Log::debug(sprintf("Curso %s - comienza procesamiento", $course['name']));
            // si no es curso SAP, o el curso no es gratuito, sigue procesando el siguiente curso
            if (strpos($course['name'], 'SAP') === false && $course['type'] != 'free') {
                continue;
            }
            Log::debug(sprintf("Curso %s - es un curso SAP o gratis", $course['name']));
            // si el estado del examen es distinto a "Sin intentos Gratis" o "X Intentos pendientes", sigue procesando el siguiente curso

            if (isset($course['certifaction_test_original']) == false) {
                continue;
            }
            Log::debug(sprintf("Curso %s - tiene estado de certificacion", $course['name']));

            $tmpCertificationPendingAttemptsFlag = stripos($course['certifaction_test_original'], 'Intentos pendientes');
            $tmpNoFreeCertificationAttemptsFlag = stripos($course['certifaction_test_original'], 'Sin intentos Gratis');
            if ($tmpCertificationPendingAttemptsFlag === false && $tmpNoFreeCertificationAttemptsFlag === false) {
                continue;
            }
            Log::debug(sprintf("Curso %s - tiene intentos pendientes o sin intentos gratis", $course['name']));
            // si el curso no tiene fecha de fin, sigue procesando el siguiente curso
            if (isset($course['end']) == false) {
                continue;
            }
            // si la fecha de fin no esta contemplada en los días de adelanto, o hay una fecha mas temprana ya cargada, sigue procesando el siguiente curso
            $tmpEndCourseDaysAhead = $processDate->diffInDays(Carbon::parse($course['end']));
            Log::debug(sprintf("Curso %s - dias de diferencia %d", $course['name'], $tmpEndCourseDaysAhead));
            // var_dump($tmpEndCourseDaysAhead);
            Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $tmpEndCourseDaysAhead: ' . $tmpEndCourseDaysAhead);
            if (in_array($tmpEndCourseDaysAhead, $validDaysAhead) == false || $tmpEndCourseDaysAhead > $endCourseDaysAhead) {
                continue;
            }
            Log::debug(sprintf("Curso %s - pasó el filtro de dias de diferencia", $course['name']));
            // "certifaction_test" puede contener: "Sin intentos Gratis", "1 Intento pendiente", "2 Intentos pendientes", "3 Intentos pendientes"

            // agrega el curso a procesar
            if (strpos($course['name'], 'SAP') !== false) :
                $sapCourses[] = $course;
            elseif ($course['type'] == 'free') :
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
            Log::debug(sprintf("llega aca: %d - %d ", count($sapCourses), count($freeCourses)));
            return null;
        endif;

        $endCourseDate = Carbon::parse($tmpEndCourseDate);
        Log::debug(sprintf("endCourseDate: %s => %s", $tmpEndCourseDate, $endCourseDate->format('d/m/Y')));
        $coursesToNotify = array_merge($sapCourses, $freeCourses);

        // Averigua el nivel de Excel sin intentos gratis
        $excelLevelWithoutFreeCertificationAttempts = null;
        $excelCourseFlag = false;
        foreach ($freeCourses as $course) :
            Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $course ' . __LINE__ . " " . serialize($course));
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
        $olderSapCourses = self::__getOlderSapCoursesStatuses($studentData['courses']);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $olderSapCourses', $olderSapCourses);
        $showOlderSapCoursesFlag = (count($olderSapCourses) > 0) ? true : false;

        // Flags para los cursos de obsequio
        $otherFreeCourses = self::__getFreeCoursesStatuses($studentData['courses']);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $otherFreeCourses', $otherFreeCourses);
        foreach ($olderSapCourses as $course) :
            if (in_array($course['status'], $irregularCourseStatuses)) { // OJO el estado a verificar es del curso SAP, no del curso de obsequio
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

        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $sapCourses', $sapCourses);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $certificationPendingAttemptsFlag: ' . $certificationPendingAttemptsFlag);
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $noFreeCertificationAttemptsFlag: ' . $noFreeCertificationAttemptsFlag);



        // Flags para la seccion de advertencia de certificacion de cursos SAP
        if ($showWarningSapCourseCertificationFlag == false) :
            // cursos SAP
            foreach ($olderSapCourses as $course) :
                if (in_array($course['status'], $irregularCourseStatuses)) {
                    $showWarningSapCourseCertificationFlag = true;
                    break;
                }
            endforeach;
            // cursos de obsequio
            foreach ($otherFreeCourses as $course) :
                if (in_array($course['status'], $irregularCourseStatuses)) {
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
        foreach ($olderSapCourses as $course) :
            if ($course['status'] == 'REPROBADO') :
                $disapprovedSapCourseNames[] = $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'APROBADO') :
                $approvedSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'POR HABILITAR') :
                $toEnableSapCourseNames[] =  $course['name'];
            elseif ($course['status'] == 'ABANDONADO') :
                $droppedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            elseif ($course['status'] == 'NO CULMINÓ') :
                $unfinishedSapCourseNames[] =  $course['name'];
                $pendingSapCoursesNames[] =  $course['name'];
            // elseif ($course['status'] == 'PENDIENTE') :
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
        Log::debug("***** Approved", $approvedSapCoursesNames);
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
            if ($course['status'] == 'POR HABILITAR') :
                $toEnableFreeCoursesCount++;
            endif;
        endforeach;

        // Flags para "intentos pendientes y sin intentos gratis"
        $pendingCoursesToNotifyNames = [];
        $noFreeAttemptsCoursesToNotifyNames = [];
        $noFreeAttemptsSapCoursesToNotifyCount = 0;
        $noFreeAttemptsFreeCoursesToNotifyCount = 0;

        foreach ($coursesToNotify as $course) :
            if(stripos($course['certifaction_test_original'], 'Intentos pendientes') !== false) :
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
            self::__addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate->copy(), 6), // agrega 6 dias habiles a la fecha de proceso
        ];

        // Fechas de cursos para habilitar
        $toEnableSapCoursesDates = [
            self::__addBusinessDaysToDate($endCourseDate->copy(), 3), // agrega 3 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate->copy(), 7), // agrega 7 dias habiles a la fecha de proceso
            self::__addBusinessDaysToDate($endCourseDate->copy(), 15), // agrega 15 dias habiles a la fecha de proceso
        ];

        // Armado del Template      
        $templateFilename =  sprintf(
            "especial-messages.sap-and-free-pending-certifications.%d-dias-%s",
            $endCourseDaysAhead,
            self::__getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag)
        );
        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $templateFilename: ' . $templateFilename);

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
            'olderSapCourses' => $olderSapCourses,
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
        $message = view($templateFilename, $s)->render();
        // elimina espacios en blanco al inicio de la linea, que se usan para jerarquias de programación
        $message = preg_replace('/^[ ]+/m', '', $message);
        // remove \n consecutives. Only two \n
        $message = preg_replace("/\n\n+/", "\n\n", $message);


        Log::debug('StudentMessageService::getMessageForSAPCourseCertification: $text: ' . $message);

        // Lógica del servicio
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
            if (in_array($course['course_status_original'], $validCourseStatus) == false) {
                continue;
            }

            $olderSapCoursesStatuses[] =
                [
                    'name' => $course['name'],
                    'status' => $course['course_status_original'],
                    'statusToDisplay' => self::courseStatusToDisplay($course['course_status_original']),
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
            if (in_array($course['course_status_original'], $validCourseStatus) == false) {
                continue;
            }

            $freeCoursesStatuses[] =
                [
                    'name' => $course['name'],
                    'status' => $course['course_status_original'],
                    'statusToDisplay' => self::courseStatusToDisplay($course['course_status_original']),
                ];
        endforeach;
        return $freeCoursesStatuses;
    }

    /**
     * Obtiene parte del nombre del archivo de template basado en los flags de certificacion de cursos
     */
    private static function __getTemplateFileNamePartForFlags($certificationPendingAttemptsFlag, $noFreeCertificationAttemptsFlag)
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
    private static function __addBusinessDaysToDate(Carbon $baseDate, $businessDaysToAdd)
    {
        // Agrega los días especificados a la fecha
        $dateResult = $baseDate->addDays($businessDaysToAdd);

        // Verifica si la fecha resultante es un domingo
        if ($dateResult->dayOfWeek == Carbon::SUNDAY) {
            // Agrega un día adicional si es domingo
            return self::__addBusinessDaysToDate($dateResult, 1);
        }

        // Verifica si la fecha resultante es feriado
        if (in_array($dateResult->toDateString(), self::__getHolidays())) {
            // Agrega un día adicional si está en el array
            return self::__addBusinessDaysToDate($dateResult, 1);
        }

        return $dateResult;
    }

    /**
     * Lee los feriados
     */
    private static function __getHolidays()
    {
        // @todo parametrizar esto en un archivo de configuración
        return [
            "2024-01-01"
        ];
    }
}
