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
$coursesToNotifyNames = array_column($coursesToNotify, 'name');


@endphp
{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 DIA PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 dia hacia delante

--}}
⚠️ ¡Atención urgente! ⏳
{{$studentData['NOMBRE']}}


@if (count($coursesToNotify) == 1)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

Es crucial que *tomes acción de inmediato para asegurar tu certificación SAP,* ya que recuerda que no emitimos certificado por participación, ni por completar algún curso.
{{-- Variante para SIN INTENTOS GRATIS --}}
🚨 Ya agotaste tus intentos gratuitos de examen, sin embargo, no todo está perdido. 🙌 Aún puedes realizar el pago por un intento adicional de examen de certificación.
🚩 Este pago lo debes realizar inmediatamente, para poder estar dentro de los plazos correspondientes y poder realizar tu examen de certificación.

*¡No pierdas más tiempo y actúa ahora!* Estamos aquí para ayudarte a obtener la anhelada certificación de SAP, antes del:
{{$endCourseDate->format('d/m/Y')}}

{{-- Cursos SAP anteriores --}}
@foreach ($otherSapCourses as $course)
    @if ($course["course_status_original"] == "CERTIFICADO")
Recuerda que antes aprobaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "REPROBADO")
Recuerda que antes reprobaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "ABANDONADO")
Recuerda que antes abandonaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "NO CULMINÓ")
Recuerda que antes no culminaste:
{{$course['name']}}
    @endif
@endforeach


{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
{{-- Filas 51 a 75: si se utilizan las filas 46, 47 y/o 48. También si se utiliza la fila 45 CON alguna de las filas desde 46 a 48.  --}}
@php
$tmpFlag = false;
foreach ($otherSapCourses as $course):
    if ($course["course_status_original"] == "REPROBADO" || $course["course_status_original"] == "ABANDONADO" || $course["course_status_original"] == "NO CULMINÓ"):
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
    $coursesToNotifyNames = array_column($coursesToNotify, 'name');


@endif

{{-- Variante para SIN INTENTOS GRATIS --}}
Te recuerdo nuevamente que tienes la opción de pagar el ponderado de tus exámenes de SAP y así certificarte, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.