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
FALTANDO 1 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 1 dias hacia delante

--}}
*¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:*
{{$student_name}}

Te envío la última información de tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@endif

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
Al momento que envío este mensaje, tus cursos están quedando:
@foreach ($coursesToNotify as $course)
{{$course['name']}}, tiene {{$course['lessons_completed']}} lecciones completas, y en total son {{$course['lessons_count']}}.
@endforeach

Por lo que *estarías perdiendo la posibilidad de realizar el pago por el ponderado* de los exámenes de certificación de {{implode(', ', $noFreeAttemptsCoursesToNotifyNames)}}, si no realizas el pago, *dentro de mi horario laboral del día de hoy.*
Y *a partir del envío de este mensaje, el tiempo mínimo para extender {{implode(', ', $pendingCoursesToNotifyNames)}}, es de 3 meses.* Y tienes como máximo el plazo de una semana a partir de hoy, para realizar el pago, solo que, el aula que recibirás estará completamente vacía, porque no guardamos tu avance posterior a las 23:59.

*Ojo, si esperas al último minuto de mi jornada laboral de hoy, no podré validar, ni solicitar tu certificado, ni realizar los trámites necesarios y tampoco realizamos devoluciones.*

{{-- SUBPLANTILLA: Avances de lecciones  --}}@include('especial-messages.sap-and-free-pending-certifications.__1day_other_sap_courses_section', ['sapCourses' => $sapCourses, 'freeCourses' => $freeCourses, 'otherSapCourses' => $otherSapCourses, 'otherFreeCourses' => $otherFreeCourses, 'toEnableSapCoursesDates'=> $toEnableSapCoursesDates, 'toEnableFreeCoursesDates'=> $toEnableFreeCoursesDates])

*Lamentamos no contar con tu participación en la certificación de Key User SAP.*

*Te recuerdo por última vez que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* No habrán devoluciones de ningún tipo, si el pago es enviado fuera de mi horario, así sea por un minuto. 

Saludos cordiales.
