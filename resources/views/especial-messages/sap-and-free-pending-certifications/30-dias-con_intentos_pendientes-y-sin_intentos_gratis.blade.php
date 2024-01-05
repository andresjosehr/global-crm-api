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

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$student_name}}

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
@php
$pendingCoursesToNotifyNames = [];
$hasSapNoFreeAttempts = false;
$freeCourseNoFreeAttemptsCount = 0;
foreach ($coursesToNotify as $course):
    if($course['noFreeAttempts'] == true && $course['isSapCourse'] == true):
        $hasSapNoFreeAttempts = true;
    endif;
    if($course['noFreeAttempts'] == true && $course['isFreeCourse'] == true):
        $freeCourseNoFreeAttemptsCount++;
    endif;
    if ($course['hasPendingAttempts'] == true):
        if($course['isExcelCourse'] == false):
            $pendingCoursesToNotifyNames[] = $course['name'];
        else:
            foreach($course['LEVELS'] as $level):
                $pendingCoursesToNotifyNames[] = $course[$level]['name'];
             endforeach;
        endif;
    endif;
endforeach;
@endphp
🤓 Te comento sobre el avance académico actual de cada curso:
@foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == false)
        ({{$course['name']}}), tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}); y aún cuentas con ({{$course["certifaction_test_original"]}}.
        @else
                @foreach($course['LEVELS'] as $level)
                ({{$course['name']}} - {{$course[$level]['name']}}), tiene ({{$course[$level]['lessons_completed']}}) lecciones completas, y en total son ({{$course[$level]['lessons_count']}}); y aún cuentas con ({{$course[$level]["certifaction_test_original"]}}.
                @endforeach
        @endif

        @if (count($pendingCoursesToNotifyNames) >= 1))
        🙌 Si no crees poder certificarte en el tiempo que te queda disponible en ({{implode(', ', $pendingCoursesToNotifyNames)}}), *NO TODO ESTÁ PERDIDO:*
        🚩 Puedes *EXTENDER el tiempo* y mantener los beneficios que tienes ahora y así lograr certificarte.
        @endif

        @if ($hasSapNoFreeAttempts == true)
        🔹 Referente a *SAP,* puedes *pagar por un intento adicional de examen,* para lograr certificarte.
        @endif

        @if ($freeCourseNoFreeAttemptsCount == 1)
        🔹 Referente a *tu curso de obsequio,* puedes pagar para *PONDERAR* los resultados de los exámenes reprobados + el avance académico completado en tu aula virtual.
        @else
        🔹 Referente a *tus cursos de obsequio,* puedes pagar para *PONDERAR* los resultados de los exámenes reprobados + el avance académico completado en tu aula virtual.
        @endif
@endforeach
Si te interesa tomar estas opciones, *te recomiendo realizar el pago en este momento,* ya que, *una semana antes de la fecha de fin,* no estará disponible.* Y tendrás que ajustarte a las nuevas condiciones.
Por favor me indicas si te interesa *y no perder el tiempo y el dinero que has invertido.*

📌 Recuerda que estos cursos finalizan el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación.
@else
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación. Tampoco emitimos certificado por nivel independiende de Excel.
@endif

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
@endif

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.