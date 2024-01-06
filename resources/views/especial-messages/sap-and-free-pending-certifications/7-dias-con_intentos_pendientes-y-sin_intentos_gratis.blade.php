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
FALTANDO 7 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 7 dias hacia delante

--}}
{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
@php 
$tmpShowSapNotification = false;
$tmpNoFreeAttemptsCourseNames = [];
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
    else:
        $tmpFreeCourseNoAttemptsCount++; // Excel es siempre gratis
        foreach($course['LEVELS'] as $level) :
            if ($course[$level]['noFreeAttempts'] == true) :
                $tmpNoFreeAttemptsCourseNames[] = sprintf("%s %s", $course['name'], $course[$level]['name']);
            endif;
        endforeach;
    endif;     
endforeach;
@endphp
💼🚀 *Tu futuro está en juego y así está quedando el avance de tus cursos:* 
{{-- SUBPLANTILLA: Avances de lecciones  --}}@include('especial-messages.sap-and-free-pending-certifications.__lessons_for_courses_section', ['coursesToNotify' => $coursesToNotify])

🙌 Si sientes que no podrás certificarte antes de la fecha de vencimiento de los cursos, *recuerda que tienes la solución: EXTENDER LA DURACIÓN DE TU AULA VIRTUAL y mantener los beneficios que tienes actualmente.*
🚩🚩 Sin embargo, *a partir de la llegada de este mensaje, el tiempo mínimo a extender es de 2 meses.* Recuerda que en mensajes anteriores te había comentado que las condiciones iban a cambiar.

@if($tmpShowSapNotification == true)
🙌 Referente a los cursos: {{implode(', ', $tmpNoFreeAttemptsCourseNames) }}, *no todo está perdido,* porque hemos conseguido una última opción para ti:
@endif
@if( $tmpFreeCourseNoAttemptsCount == 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tu curso de obsequio, a un precio super especial.
@elseif ( $tmpFreeCourseNoAttemptsCount > 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tus cursos de obsequio, a un precio super especial.
@endif

*Responde inmediatamente. Tu futuro está en juego.* 💼🚀 Y la fecha de fin de los cursos es el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. ¡Persiste y alcanza tus metas! 🌟
@else
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. Además, no otorgamos certificados por niveles individuales de Excel. ¡Persiste y alcanza tus metas! 🌟
@endif
No dejes que el tiempo se agote⏳. *Actúa ahora y asegúrate de mantener tu camino hacia la certificación.*


{{-- SUBPLANTILLA: Cursos SAP anteriores --}}
@include('especial-messages.sap-and-free-pending-certifications.__other_sap_courses_section', ['sapCourses' => $sapCourses, 'otherSapCourses' => $otherSapCourses, 'otherFreeCourses' => $otherFreeCourses])


⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
