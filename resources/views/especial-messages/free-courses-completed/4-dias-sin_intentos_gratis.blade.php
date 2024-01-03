{{--

"PLANTILLAS CURSO OBSEQUIOS CURSANDO CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: FREE
ESTADO CURSO: CURSANDO
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
@if (count($coursesToNotify) == 1)
¡Urgente, tu curso está en peligro! ⚠️

Tenemos importantes noticias sobre las condiciones de tu curso:
@else
¡Urgente, tus cursos están en peligro! ⚠️

Tenemos importantes noticias sobre las condiciones de tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- VAriante solo Cursos PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
    🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no contaremos con tu participación en esta certificación.
    @else
    🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no contaremos con tu participación en estas certificaciones.
    @endif
@endif

{{-- VAriante para Cursos PBI o MSP, Y EXCEL --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no estaremos contando con tu participación en la certificación de estos cursos porque están reprobados, ya que Excel tiene:
    @foreach ($coursesToNotify as $course)
    @if ($course['isExcelCourse'] == true)
        @foreach($course['LEVELS'] as $level)
            @if ($course[$level]['noFreeAttempts'] == true)
                El nivel {{$course[$level]['name']}}: reprobado.
            @endif
        @endforeach
    @endif
@endforeach

@endif


{{-- VAriante solo Cursos EXCEL --}}
@if ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
    @if (count($tmpLevels) == 1)
    🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no estaremos contando con tu participación en la certificación, porque reprobaste el nivel:
    @elseif (count($tmpLevels) > 1)
    🚨 Recuerda que te comentamos la opción de optar por el ponderado, pero no hemos recibido tu pago, lo cual es una lástima, porque no estaremos contando con tu participación en la certificación, porque reprobaste los niveles:
    @endif
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
                @if ($course[$level]['noFreeAttempts'] == true)
                    El nivel {{$course[$level]['name']}}: reprobado.
                @endif
            @endforeach
        @endif
    @endforeach
@endif


{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)
👀 *OJO aún estás cursando:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'CURSANDO')
        {{$course['name']}}
        @endif
    @endforeach
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

{{-- Filas 567 a 591: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
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

🚩 🚩 *Si tienes más dudas de esta condición, consúltame para explicarte y puedas tomar tus decisiones.*
📌 Ya que este pago, lo debes realizar antes del:
{{$endCourseDate->format('d/m/Y')}}

⚠️ Te informamos que, si decides esperar hasta el último día para realizar el pago del ponderado, *tendrás que hacerlo dentro de mi horario laboral,* ya que el sistema elimina automáticamente tus accesos a las 23:59 horas.
Esto significa que no se conservará ningún respaldo de tu progreso y lamentablemente no podremos aceptar capturas de pantalla como prueba para apoyarte con la solicitud de ponderar tus exámenes.

*Si en dado caso decides realizar el pago fuera de mi horario laboral, el mismo no será reconocido y tu aula será eliminada porque no habrá quien reporte tu pago:*
*Teniendo así que ajustarte a las condiciones de extensión o rematrícula (dependiendo del caso) completando el valor faltante, ya que no realizaremos ninguna devolución por el pago realizado.*

Si en dado caso *no puedes pagar el ponderado, indícame para buscar opciones juntos.*

*Aprovecho para comentarte que toda solicitud y pago de ponderado, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*