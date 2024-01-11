@php
// cache interna
$otherFreeCoursesInProgressNames = [];
$otherFreeCoursesDissaprovedNames = [];
$otherFreeCoursesDroppedNames = [];
$otherFreeCoursesUnfinishedNames = [];
$otherFreeCoursesApprovedNames = [];
$otherFreeCoursesToEnableNames = [];
foreach($otherFreeCourses as $course):

   switch ($course['course_status']) {
        case 'CURSANDO':
            $otherFreeCoursesInProgressNames[] = $course['name'];
            break;
        case 'REPROBADO':
            $otherFreeCoursesDissaprovedNames[] = $course['name'];
            break;
        case 'ABANDONADO':
            $otherFreeCoursesDroppedNames[] = $course['name'];
            break;
        case 'NO CULMINÓ':
            $otherFreeCoursesUnfinishedNames[] = $course['name'];
            break;
        case 'APROBADO':
            $otherFreeCoursesApprovedNames[] = $course['name'];
            break;
        case 'POR HABILITAR':
            $otherFreeCoursesToEnableNames[] = $course['name'];
            break;
    }
endforeach;


// cache interna
$otherSapCoursesInProgressNames = [];
$otherSapCoursesDissaprovedNames = [];
$otherSapCoursesDroppedNames = [];
$otherSapCoursesUnfinishedNames = [];
$otherSapCoursesApprovedNames = [];
$otherSapCoursesToEnableNames = [];
$otherSapCoursesCertifiedNames = [];
foreach($otherSapCourses as $course):

   switch ($course['course_status']) {
        case 'CURSANDO':
            $otherSapCoursesInProgressNames[] = $course['name'];
            break;
        case 'REPROBADO':
            $otherSapCoursesDissaprovedNames[] = $course['name'];
            break;
        case 'ABANDONADO':
            $otherSapCoursesDroppedNames[] = $course['name'];
            break;
        case 'NO CULMINÓ':
            $otherSapCoursesUnfinishedNames[] = $course['name'];
            break;
        case 'APROBADO':
            $otherSapCoursesApprovedNames[] = $course['name'];
            break;
            case 'POR HABILITAR':
            $otherSapCoursesToEnableNames[] = $course['name'];
            break;
            case 'CERTIFICADO':
            $otherSapCoursesCertifiedNames[] = $course['name'];
            break;
    }
endforeach;

$coursesToNotifyNames = array_column($coursesToNotify, 'name');
$sapCoursesNames = array_column($sapCourses, 'name');


@endphp
{{--

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 4 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 4 dias hacia delante

--}}
¡Urgente, *tus certificaciones están en peligro!* ⚠️
Tenemos importantes noticias sobre las *condiciones actuales de tus cursos:*
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

*Iremos directo al grano para no agobiarte con textos largos:*
{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}🤓 Este es el estado actual de tus cursos:
{{-- SUBPLANTILLA: Avances de lecciones  --}}@include('especial-messages.sap-and-free-pending-certifications.__lessons_for_courses_section', ['coursesToNotify' => $coursesToNotify])

@php 
$tmpShowSapNotification = false;
$tmpNoFreeAttemptsCourseNames = [];
$tmpPendingAttemptsCourseNames = [];
$tmpFreeCourseNoAttemptsCount = 0;
foreach ($coursesToNotify as $course) :
    if($course['isExcelCourse'] == false) :
        if ($course['noFreeAttempts'] == true) :
            $tmpNoFreeAttemptsCourseNames[] = $course['name'];
        endif;
        if($course['isSapCourse'] == true && $course['noFreeAttempts'] == true) :
            $tmpShowSapNotification = true;
        endif;
        if($course['isFreeCourse'] == true && $course['noFreeAttempts'] == true) :
            $tmpFreeCourseNoAttemptsCount++;
        endif;
        if($course['hasPendingAttempts'] == true) :
            $tmpPendingAttemptsCourseNames[] = $course['name'];
        endif;
    else:
        $tmpFreeCourseNoAttemptsCount++; // Excel es siempre gratis
        foreach($course['LEVELS'] as $level) :
            if ($course[$level]['noFreeAttempts'] == true) :
                $tmpNoFreeAttemptsCourseNames[] = sprintf("%s %s", $course['name'], $course[$level]['name']);
            endif;
            if($course[$level]['hasPendingAttempts'] == true) :
                $tmpPendingAttemptsCourseNames[] = sprintf("%s %s", $course['name'], $course[$level]['name']);
            endif;
        endforeach;
    endif;     
endforeach;
@endphp
🚨 Ya sabes que no emitimos certificado por haber completado el curso, ni por participación. *Y tú aún no te has certificado, a pesar de brindarte intentos gratuitos.* 
Todavía HOY puedes extender por DOS MESES {{implode(', ', $tmpPendingAttemptsCourseNames) }}, pero en pocos días, la extensión mínima es de 3 meses *sin excepción.*

@if( $tmpFreeCourseNoAttemptsCount == 1)
🚨 Además, puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual y obtener el certificado de {{implode(', ', $tmpNoFreeAttemptsCourseNames) }} en un máximo de 48 horas hábiles.
@elseif ( $tmpFreeCourseNoAttemptsCount > 1)
🚨 Además, puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual y obtener los certificados de {{implode(', ', $tmpNoFreeAttemptsCourseNames) }} en un máximo de 48 horas hábiles.
@endif

📌 *¡No pierdas más tiempo y realiza el pago en este momento!* Ya que, si esperas a los próximos días, perderás esta posibilidad.

La fecha de fin de los cursos es el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- SUBPLANTILLA: Cursos SAP anteriores --}}
@include('especial-messages.sap-and-free-pending-certifications.__other_sap_courses_section', ['sapCourses' => $sapCourses, 'otherSapCourses' => $otherSapCourses, 'otherFreeCourses' => $otherFreeCourses])



⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
