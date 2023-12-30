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

@if (count($coursesToNotify) == 1)
🤓 Hasta los momentos tu avance académico en el aula virtual es el siguiente:
Tienes ({{$coursesToNotify[0]['lessons_completed']}}) lecciones completas, y en total son ({{$coursesToNotify[0]['lessons_count']}}).
@else
    @if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
🤓 Hasta los momentos, el avance académico de cada curso, es el siguiente:
    @elseif ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🤓 Hasta los momentos, el avance académico de cada curso con los niveles de Excel, es el siguiente:
    @elseif ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
🤓 Hasta los momentos, el avance académico de cada nivel, es el siguiente:
    @endif
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == false)
        {{$course['name']}}, tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
        @else
            @foreach($course['LEVELS'] as $level)
                {{$course['name']}} - {{$course[$level]['name']}}, tiene ({{$course[$level]['lessons_completed']}}) lecciones completas, y en total son ({{$course[$level]['lessons_count']}}).
            @endforeach
        @endif
    @endforeach
@endif

@if (count($coursesToNotify) == 1)
📌 Este curso finaliza el día:
@else
📌 Estos cursos finalizan el día:
@endif
{{$endCourseDate->format('d/m/Y')}}

@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
🚩 Recuerda que no brindamos certificados por participación, ni por haber completado el curso.
    @else
🚩 Recuerda que no brindamos certificados por participación, ni por haber completado los cursos.
    @endif
@elseif ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
🚩 Recuerda que no brindamos certificados por participación, ni por haber completado los cursos. Tampoco brindamos certificados por niveles independientes de Excel.
@elseif ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
🚩 Recuerda que no brindamos certificados por participación, ni por haber completado el curso. Tampoco brindamos certificados por niveles independientes de Excel.
@endif

{{-- Fila 30: solo si AULA SAP tiene el estado CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
@if($studentData["AULA SAP"]  == "CURSANDO" && $studentData["CERTIFICADO"] != "EMITIDO")
🙌 Si no crees poder certificarte, tenemos una solución para ti: *puedes EXTENDER EL TIEMPO* y mantener los beneficios que tienes ahora, y así no perder la oportunidad de certificarte, cuando apruebes tu curso de SAP.
{{-- Fila 31: solo si EXAMEN SAP tiene el estado APROBADO, o en ESTADO PONDERADO dice PAGADO --}}
@elseif($studentData["EXAMEN"]  == "APROBADO" || $studentData["PONDERADO SAP"] != "PAGADO")
🙌 Si no crees poder certificarte, tenemos una solución para ti: *puedes EXTENDER EL TIEMPO* y mantener los beneficios que tienes ahora, y así no perder la oportunidad de certificarte.
@endif

*Si esperas a la última semana para realizar el pago, tendrás que ajustarte a las nuevas condiciones de extensión.*


{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)
👀 *OJO también estás cursando:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'CURSANDO')
{{$course['name']}}
        @endif
    @endforeach
    {{-- Fila 38: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP.
    {{-- Fila 39: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y si es curso OBSEQUIO CURSANDO --}}
    @elseif(($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro este curso, si no te certificas en:
    @endif
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach
@endif


{{-- VARIANTE Filas 43 a 55: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showDissaprovedOtherCourses == true )
👀 *OJO completaste, pero reprobaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'REPROBADO')
{{$course['name']}}
        @endif
    @endforeach
@endif
@if($showDissaprovedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
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

{{-- VARIANTE Filas 57 a 67: Fila 57: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDissaprovedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no te certificas en:

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


{{-- VARIANTE Filas 70 a 82: Filas 70 a 94: si tiene curso obsequio con estado NO CULMINÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showUnfinishedOtherCourses == true )
👀 *OJO: recuerda que no culminaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'NO CULMINÓ')
{{$course['name']}}
        @endif
    @endforeach
@endif
{{-- Fila 72: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO NO CULMINÓ --}}
@if($showUnfinishedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
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

{{-- VARIANTE Filas 84 a 94: Fila 57: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDissaprovedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no te certificas en:

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

{{-- VARIANTE Filas Filas 97 a 121: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showDroppedOtherCourses == true )
👀 *OJO: recuerda que abandonaste:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'ABANDONADO')
{{$course['name']}}
        @endif
    @endforeach
@endif
{{-- Fila 99: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ --}}
@if($showDroppedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
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

{{-- VARIANTE Filas 84 a 94: Fila 111: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDroppedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no te certificas en:
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






{{-- VARIANTE Filas 124 a 144: si tiene curso obsequio con estado POR HABILITAR, con las condiciones específicas de cada fila: --}}
@if($showToEnableOtherCourses == true )
👀 *OJO tienes por habilitar:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
{{$course['name']}}
        @endif
    @endforeach
@endif
{{-- Fila 99: Fila 126: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ --}}
@if($showToEnableOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
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

{{-- VARIANTE Fila 136: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showToEnableOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no te certificas en:
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
⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 

@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
*Por este motivo, debes indicarme al aprobar algún examen, de lo contrario, podrías perder el certificado el día de la fecha de fin antes mencionada.*
@else
*Por este motivo, debes indicarme al aprobar algún examen, de lo contrario, podrías perder los certificados el día de la fecha de fin antes mencionada.*
@endif
*Recuerda también que para poder obtener este certificado, luego de haber aprobado, también debes aprobar SAP.*

*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.


Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.

