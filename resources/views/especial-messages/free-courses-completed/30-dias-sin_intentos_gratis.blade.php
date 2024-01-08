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
¡Hola!
{{$studentData['NOMBRE']}}

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
    🚨 Actualmente este curso se encuentra reprobado y no brindamos certificados por participación.
    @else
    🚨 Actualmente estos cursos se encuentran reprobados y no brindamos certificados por participación.
    @endif
@endif

{{-- VAriante para Cursos PBI o MSP, Y EXCEL --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🚨 Actualmente estos cursos *se encuentran reprobados y para Excel, debes aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente, y este es el estado de cada nivel del curso:
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
NIVEL {{$course[$level]['name']}} {{$course[$level]['course_status']}}
            @endforeach
        @endif
    @endforeach
@endif


{{-- VAriante solo Cursos EXCEL --}}
@if ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
🚨 A continuación te indico el estado actual de cada nivel:
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
NIVEL {{$course[$level]['name']}} {{$course[$level]['course_status']}}
            @endforeach
        @endif
    @endforeach
Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente
@endif

🚩 🚩 *Pero no todo está perdido.*
*Puedes realizar el pago para ponderar los intentos de examen que reprobaste*

{{-- VAriante hay algun Curso EXCEL --}}
@php
    $tmpCourses = [];
    $tmpLevels = [];
    // asume que los cursos son "sin intentos gratis"
    foreach ($coursesToNotify as $course) :
        if ($course['isExcelCourse'] == true) :
            foreach($course['LEVELS'] as $level) :
                if ($course[$level]['noFreeAttempts'] == true) :
                    $tmpLevels[] = $course[$level]['name'];
                endif;
            endforeach;
        else:
            $tmpCourses[] = $course['name'];
        endif;
    endforeach;    
@endphp
@if ($hasSpecializedCoursesToNotify == true && count($tmpLevels) == 1)
De {{implode(', ', $tmpCourses)}} y del nivel {{implode(', ', $tmpCourses)}} de Excel.
@elseif ($hasSpecializedCoursesToNotify == false && count($tmpLevels) > 1)
De los niveles {{implode(', ', $tmpLevels)}} de Excel.
@endif

{{-- Fila 46: solo si AULA SAP tiene el estado CURSANDO y es más de un curso de obsequio o es solo Excel --}}
@if($studentData["AULA SAP"] == "CURSANDO" && (count($otherFreeCourses) > 1))
Y de esta manera obtener tus certificados cuando te certifiques en SAP.
{{-- Fila 47: solo si EXAMEN SAP tiene el estado APROBADO, o en ESTADO PONDERADO dice PAGADO y es más de un curso de obsequio o es solo Excel --}}
@elseif(($studentData["EXAMEN"] == "APROBADO" || $studentData["PONDERADO SAP"] != "PAGADO") && (count($otherFreeCourses) > 1))
Y de esta manera obtener tus certificados.
@endif

{{-- VAriante solo Cursos EXCEL --}}
@if ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true && $course['APPROVED_LEVELS_COUNT'] == 1)
¡Estás a tan solo un paso de lograrlo! Ya tienes aprobado un nivel, no pierdas la oportunidad.
        @elseif ($course['isExcelCourse'] == true && $course['APPROVED_LEVELS_COUNT'] == 2)
¡Estás a tan solo un paso de lograrlo! Ya tienes aprobados dos niveles, no pierdas la oportunidad.
        @endif
    @endforeach
@endif

{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)
👀 *OJO también estás cursando:*
{{implode(', ', $otherFreeCoursesInProgressNames)}}
    {{-- Fila 56: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO CURSANDO --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que podrías perder el acceso, a pesar de haber iniciado, si no pagas el ponderado de:
    {{-- Fila 57: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y si es curso OBSEQUIO CURSANDO --}}
    @elseif(($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro el acceso, si no pagas el ponderado de:
    @endif
    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach
@endif


{{-- VARIANTE Filas 61 a 85: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showDissaprovedOtherCourses == true )
👀 *OJO completaste, pero reprobaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'REPROBADO')
        {{$course['name']}}
        @endif
    @endforeach

    {{-- Fila 63: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO COMPLETA REPROBADO o SIN INTENTOS GRATIS, que TERMINE en OTRA FECHA o ya haya terminado. Se tomará EXCEL como reprobado, si tiene al menos un nivel reprobado.  --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:
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

    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @endif
@endif

{{-- Fila 75: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDissaprovedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') !== false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:

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

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
@endif


{{-- VARIANTE Filas 88 a 112: si tiene curso obsequio con estado NO CULMINÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila:--}}
@if($showUnfinishedOtherCourses == true )
    👀 *OJO: recuerda que no culminaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'NO CULMINÓ')
        {{$course['name']}}
        @endif
    @endforeach

    {{-- Fila 90: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO NO CULMINÓ --}}
    @if($showUnfinishedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:
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
@endif

{{-- VARIANTE Fila 102: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDissaprovedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:

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

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
@endif

{{-- VARIANTE Filas 115 a 139: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showDroppedOtherCourses == true )
    👀 *OJO: recuerda que abandonaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'ABANDONADO')
        {{$course['name']}}
        @endif
    @endforeach

    {{-- Fila 117: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ  --}}
    @if($showDroppedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:
        @foreach ($coursesToNotify as $course)
        {{$course['name']}}
        @endforeach
    @endif

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

{{-- VARIANTE Fila 129: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDroppedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:
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

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
@endif






{{-- VARIANTE Filas 143 a 163: si tiene curso obsequio con estado POR HABILITAR, con las condiciones específicas de cada fila: --}}
@if($showToEnableOtherCourses == true )
    👀 *OJO tienes por habilitar:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        {{$course['name']}}
        @endif
    @endforeach

    {{-- Fila 99: Fila 126: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ --}}
    @if($showToEnableOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no realizas el pago del ponderado de:
        @foreach ($coursesToNotify as $course)
        {{$course['name']}}
        @endforeach
    @endif

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

{{-- VARIANTE Fila 155: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showToEnableOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no realizas el pago del ponderado de:
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

Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
@endif


*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*
📌 Ya que este pago, lo debes realizar antes del:
{{$endCourseDate->format('d/m/Y')}}

⚠️ Recuerda que este día, se eliminarán tus accesos de manera automática a las 23:59. 

*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*

Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.
