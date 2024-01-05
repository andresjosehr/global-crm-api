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
@if (count($coursesToNotify) > 1)
¡Urgente, *tu certificación SAP está en peligro!* ⚠️
Tenemos importantes noticias sobre las *condiciones actuales de tu curso:*
@else
¡Urgente, *tus certificaciones SAP están en peligro!* ⚠️
Tenemos importantes noticias sobre las *condiciones actuales de tus cursos:*
@endif

@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

*Iremos directo al grano para no agobiarte con textos largos:*
{{-- Variante para SIN INTENTOS GRATIS --}}
🚨 Puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual y obtener tu certificado en un máximo de 48 horas hábiles.

🚩 *Si no realizas tu pago hoy, no podremos ayudarte luego y habrás perdido todo.*

Aprovecha que aún tienes posibilidades de salir adelante como Key User SAP y no dar todo por perdido.
Recuerda que si esperas a tu fecha fin:
{{$endCourseDate->format('d/m/Y')}}

{{-- Variante para SIN INTENTOS GRATIS --}}
No podremos ponderar tus resultados fuera de nuestro horario laboral, aunque envíes capturas.

{{-- Cursos SAP anteriores --}}
@foreach ($otherSapCourses as $course)
    @if ($course["course_status"] == "CERTIFICADO")
Recuerda que antes aprobaste:
{{$course['name']}}
    @elseif ($course["course_status"] == "REPROBADO")
Recuerda que antes reprobaste:
{{$course['name']}}
    @elseif ($course["course_status"] == "ABANDONADO")
Recuerda que antes abandonaste:
{{$course['name']}}
    @elseif ($course["course_status"] == "NO CULMINÓ")
Recuerda que antes no culminaste:
{{$course['name']}}
    @endif
@endforeach


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
    $coursesToNotifyNames = array_column($coursesToNotify, 'name');


@endif

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
