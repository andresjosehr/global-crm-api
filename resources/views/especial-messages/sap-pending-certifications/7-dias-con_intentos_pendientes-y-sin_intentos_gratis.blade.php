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


@endphp
{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 DIA PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 dia hacia delante

--}}
{{$studentData['NOMBRE']}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳

@if (count($coursesToNotify) == 1)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
💼🚀 *Tu futuro está en juego:* 
*Este es el estado actual de tus cursos SAP habilitados:*
{{implode("\n", $coursesToNotifyNames)}}

{{-- Variante para INTENTOS PENDIENTES --}}
@foreach ($coursesToNotify as $course)
    @if ($course['noFreeAttempts'] == true)
🚨 Completaste, pero no aprobaste el examen, con los 3 intentos que te ofrecimos de manera gratuita.
🙌 Te comento que *no todo está perdido,* porque hemos conseguido una solución adicional para ti:
🚨 Puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual.
*Posterior a tu pago, en máximo 48 horas hábiles tendrás el certificado y la insignia digital respaldada por Credly.*
📌 *¡No pierdas más tiempo y realiza el pago en este momento!* Ya que, si esperas a los próximos días, perderás esta posibilidad porque tu aula se elimina el día:
    @elseif ($course['hasPendingAttempts'] == true)
💼🚀 *Así está tu avance académico:* 
{{$course['name']}}, tiene (CANTIDAD DE LECCIONES COMPLETAS EN EL AULA) lecciones completas, y en total son (TOTAL DE LECCIONES DEPENDIENDO DEL CURSO).
Y tus cursos finalizan el día:
    @endif
@endforeach
{{$endCourseDate->format('d/m/Y')}}

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
{{-- Variante para INTENTOS PENDIENTES --}}
🙌 Aún *tienes una solución en tus manos,* todavía puedes extender el curso, solo que *ya no puedes hacerlo por 1 mes.
*El tiempo mínimo de extensión en este momento, es por 2 meses.* Recuerda que esta información crítica fue enviada anteriormente.

No dejes que esta oportunidad escape de tus manos. ¿Deseas extender el plazo y asegurar tu certificación? Responde inmediatamente. 

Si esperas a que finalice tu curso, tendrás 1 semana para extender y será *por un mínimo de 3 meses y el aula que recibirás estará completamente vacía, perdiendo así el avance que tenías anteriormente.* 
Y pasada esta última semana de plazo, tendrás que volver a matricularte al precio regular del curso. 


{{-- Cursos SAP anteriores --}}
@if(count($otherSapCourses) > 0)
    @if(count($otherSapCoursesCertifiedNames) > 0)
Recuerda que antes aprobaste:
{{implode("\n", $otherSapCoursesCertifiedNames)}}
    @endif
    @if(count($otherSapCoursesDissaprovedNames) > 0)
    Recuerda que antes reprobaste:
{{implode("\n", $otherSapCoursesDissaprovedNames)}}
    @endif
    @if(count($otherSapCoursesDroppedNames) > 0)
    Recuerda que antes abandonaste:
{{implode("\n", $otherSapCoursesDroppedNames)}}
    @endif    
    @if(count($otherSapCoursesUnfinishedNames) > 0)
    Recuerda que antes no culminaste:
{{implode("\n", $otherSapCoursesUnfinishedNames)}}  
    @endif
@endif


{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
{{-- Filas 51 a 75: si se utilizan las filas 46, 47 y/o 48. También si se utiliza la fila 45 CON alguna de las filas desde 46 a 48.  --}}
@php
$tmpFlag = false;
foreach ($otherSapCourses as $course):
    if ($course["course_status"] == "REPROBADO" || $course["course_status"] == "ABANDONADO" || $course["course_status"] == "NO CULMINÓ"):
        $tmpFlag = true;
    endif;
endforeach;

$tmpShowSapSectionFlag = ($tmpFlag || count($otherFreeCoursesDissaprovedNames) > 0 || count($otherFreeCoursesDroppedNames) > 0 || count($otherFreeCoursesUnfinishedNames) > 0) ? true : false;

@endphp
@if ($tmpFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus cursos de obsequio te comento:*
    @if(count($otherFreeCoursesInProgressNames) > 0)
Aún estás *cursando:*
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesDissaprovedNames) > 0)
Completaste pero *REPROBASTE:*
{{implode("\n", $otherFreeCoursesDissaprovedNames)}}
    @endif
    @if(count($otherFreeCoursesUnfinishedNames) > 0)
*No culminaste:*
{{implode("\n", $otherFreeCoursesUnfinishedNames)}}
    @endif
    @if(count($otherFreeCoursesDroppedNames) > 0)
*Abandonaste:*
{{implode("\n", $otherFreeCoursesDroppedNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
Aún tienes *por habilitar:*
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
*Aprobaste:*
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif

    @if ($tmpShowSapSectionFlag == true)
        @if (count($coursesToNotify) == 1)
        Por lo que, si no te certificas en este curso SAP:
        @else 
    Por lo que, si no te certificas en estos cursos SAP:
        @endif

        {{implode("\n", $coursesToNotifyNames)}}

        @if(count($otherFreeCoursesInProgressNames) > 0)
        A pesar de haberlo iniciado, pierdes el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
        @endif
        @if(count($otherFreeCoursesApprovedNames) > 0)
        Pierdes el acceso al certificado de:
    {{implode("\n", $otherFreeCoursesApprovedNames)}}
        @endif
        @if(count($otherFreeCoursesToEnableNames) > 0)
        Y ya no podrás habilitar:
    {{implode("\n", $otherFreeCoursesToEnableNames)}}
        @endif    
    @endif

@endif

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
💭 Piensa la opción de realizar el pago para extender SAP y/o el ponderado de los exámenes de certificación, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
