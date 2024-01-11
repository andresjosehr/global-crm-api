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

"PLANTILLAS CURSO OBSEQUIOS CURSANDO CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: FREE
ESTADO CURSO: CURSANDO
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

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

{{-- VAriante solo Cursos PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
    🚨 Una vez más te indico que este curso está reprobado y que aún no has optado por realizar el pago del ponderado.
    @else
    🚨 Una vez más te indico que estos cursos están reprobados y que aún no has optado por realizar el pago del ponderado.
    @endif
@endif

{{-- VAriante para Cursos PBI o MSP, Y EXCEL --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🚨 Una vez más te indico que estos cursos están reprobados y *para Excel, debes aprobar los 3 niveles,* y este es el estado de cada nivel:
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
NIVEL {{$course[$level]['name']}} {{$course[$level]['course_status']}}
            @endforeach
        @endif
    @endforeach
Y aún no has optado por realizar el pago del ponderado.
@endif


{{-- VAriante solo Cursos EXCEL --}}
@if ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
🚨 Necesito que sepas el estado actual de cada nivel:
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
NIVEL {{$course[$level]['name']}} {{$course[$level]['course_status']}}
            @endforeach
        @endif
    @endforeach
    Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por nivel independiente. Así que en este momento, el curso se encuentra *REPROBADO,* ya que aún no has optado por el pago del ponderado.
@endif

Recuerda que no brindamos certificado, solo por participación.

🚩 🚩 Si aún estás considerando realizar tu pago, te recuerdo que debe ser en estos días, ya que la fecha fin es el:
{{$endCourseDate->format('d/m/Y')}}


Y este día a las 23:59, se eliminaría tu aula y no tendríamos cómo obtener los exámenes realizados para así ponderarlos y proceder a emitir algún certificado.

Ten en cuenta lo siguiente:


{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)
👀 *Como aún no te has certificado en SAP y aún estás cursando:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'CURSANDO')
        {{$course['name']}}
        @endif
    @endforeach

    Esto significa que, incluso si has comenzado, corres el riesgo de perder el acceso si no completas el pago del ponderado de:

    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach
@endif


{{-- VARIANTE Filas 61 a 85: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showDissaprovedOtherCourses == true )
👀 *Como aún no te has certificado en SAP y completaste, pero reprobaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'REPROBADO')
        {{$course['name']}}
        @endif
    @endforeach

    Si no realizas el pago del ponderado de:
    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach    

    {{-- Fila 63: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO COMPLETA REPROBADO o SIN INTENTOS GRATIS, que TERMINE en OTRA FECHA o ya haya terminado. Se tomará EXCEL como reprobado, si tiene al menos un nivel reprobado.  --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
        @foreach ($otherFreeCourses as $course)
            @if ($course['course_status_original'] == 'POR HABILITAR')
            A pesar de quedar pendiente, no podrás habilitar:
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'CURSANDO')
            A pesar de haber iniciado, perderías el acceso a:
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'APROBADO')
            A pesar de haber aprobado, perderías el acceso al certificado internacional:
            {{$course['name']}}
            @endif
        @endforeach

    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @endif
@endif




{{-- VARIANTE Filas 88 a 112: si tiene curso obsequio con estado NO CULMINÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila:--}}
@if($showUnfinishedOtherCourses == true )
👀 *Como aún no te has certificado en SAP y no culminaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'NO CULMINÓ')
        {{$course['name']}}
        @endif
    @endforeach

    Si no realizas el pago del ponderado de:
    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach   

    {{-- Fila 90: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO NO CULMINÓ --}}
    @if($showUnfinishedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )

        @foreach ($otherFreeCourses as $course)
            @if ($course['course_status_original'] == 'POR HABILITAR')
            A pesar de quedar pendiente, no podrás habilitar:
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'CURSANDO')
            A pesar de haber iniciado, perderías el acceso a:
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'APROBADO')
            A pesar de haber aprobado, perderías el acceso al certificado internacional:
            {{$course['name']}}
            @endif
        @endforeach

    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @endif
@endif



{{-- VARIANTE Filas 115 a 139: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showDroppedOtherCourses == true )
👀 *Como aún no te has certificado en SAP y abandonaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'ABANDONADO')
        {{$course['name']}}
        @endif
    @endforeach

    Si no realizas el pago del ponderado de:
    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach  

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        A pesar de quedar pendiente, no podrás habilitar:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'CURSANDO')
        A pesar de haber iniciado, perderías el acceso a:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'APROBADO')
        A pesar de haber aprobado, perderías el acceso al certificado internacional:
        {{$course['name']}}
        @endif
    @endforeach

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif



{{-- VARIANTE Filas 143 a 163: si tiene curso obsequio con estado POR HABILITAR, con las condiciones específicas de cada fila: --}}
@if($showToEnableOtherCourses == true )
👀 *Como aún no te has certificado en SAP y tienes por habilitar:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        {{$course['name']}}
        @endif
    @endforeach

    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:
        @foreach ($coursesToNotify as $course)
        {{$course['name']}}
        @endforeach

    @foreach ($otherFreeCourses as $course)
    @if ($course['course_status_original'] == 'CURSANDO')
    A pesar de haber iniciado, perderías el acceso a:
    {{$course['name']}}
    @elseif ($course['course_status_original'] == 'APROBADO')
    A pesar de haber aprobado, perderías el acceso al certificado internacional:
    {{$course['name']}}
    @endif
    @endforeach

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif

Recuerda tener en cuenta para todo lo mencionado anteriormente, que existe la condición donde no se permite tener más de un curso con resultados *reprobados o abandonados.*

📌 No dejes que esta oportunidad escape de tus manos. *Responde inmediatamente. Tu futuro está en juego.* 💼🚀
*Si en dado caso no puedes pagar el ponderado, indícame para buscar opciones juntos.*

⚠️ *Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*

Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.

