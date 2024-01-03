{{--

"PLANTILLAS CURSO OBSEQUIOS CURSANDO CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: FREE
ESTADO CURSO: CURSANDO
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

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

{{-- VAriante solo Cursos PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
    🚨 Sé que reprobaste este curso y lamentablemente no brindamos certificados por participación.
    @else
    🚨 Sé que reprobaste estos cursos y lamentablemente no brindamos certificados por participación.
    @endif
@endif

{{-- VAriante para Cursos PBI o MSP, Y EXCEL --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🚨 Sé que reprobaste estos cursos, y *para Excel, debes aprobar los 3 niveles,* porque no brindamos certificados por nivel independiente, y este es el estado de cada nivel:
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
🚨 Necesito que sepas el estado actual de cada nivel:
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
NIVEL {{$course[$level]['name']}} {{$course[$level]['course_status']}}
            @endforeach
        @endif
    @endforeach
    Recordándote que debes *aprobar los 3 niveles,* porque no brindamos certificados por participación, ni por nivel independiente. Así que, en este momento, el curso se encuentra *REPROBADO.*
@endif

🚩 🚩 *Pero todavía hay posibles soluciones:*
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

{{-- Fila 46: solo si AULA SAP tiene el estado CURSANDO--}}
@if($studentData["AULA SAP"] == "CURSANDO" && (count($coursesToNotify) > 1))
Y de esta manera obtener tus certificados cuando te certifiques en SAP.
{{-- Fila 47: solo si EXAMEN SAP tiene el estado APROBADO, o en ESTADO PONDERADO dice PAGADO y es más de un curso de obsequio o es solo Excel --}}
@elseif(($studentData["EXAMEN"] == "APROBADO" || $studentData["PONDERADO SAP"] != "PAGADO") && (count($coursesToNotify) > 1))
Y de esta manera obtener tus certificados.
@if($studentData["AULA SAP"] == "CURSANDO" && (count($coursesToNotify) == 1))
Y de esta manera obtener tu certificado.
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
👀 *OJO, como aún no te has certificado en SAP y aún estás cursando:*
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
👀 *OJO como aún no te has certificado en SAP y completaste, pero reprobaste:*
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
👀 *OJO: como aún no te has certificado en SAP y no culminaste:*
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
👀 *OJO: como aún no te has certificado en SAP y abandonaste:*
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
👀 *OJO como aún no te has certificado en SAP y tienes por habilitar:*
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

*Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*
📌 Ya que este pago, lo debes realizar antes del:
{{$endCourseDate->format('d/m/Y')}}

⚠️ Recuerda que este día, se eliminarán tus accesos de manera automática a las 23:59. 

*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*

Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.

