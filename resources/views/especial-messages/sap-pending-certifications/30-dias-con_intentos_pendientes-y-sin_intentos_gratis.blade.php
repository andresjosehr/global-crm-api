{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$student_name}}

@if (count($coursesToNotify) > 1)
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
@foreach ($coursesToNotify as $course)
{{$course['name']}}
    @if ($course['status'] == 'SIN INTENTOS GRATIS')
🚨 Completaste, pero no aprobaste el examen, con los 3 intentos que te ofrecimos de manera gratuita.
🙌 *Pero NO TODO ESTÁ PERDIDO,* puedes *pagar por un intento adicional de examen* y así certificarte, recuerda que no brindamos certificado por participación, ni por haber completado el curso.
    @elseif ($course['status'] == 'INTENTOS PENDIENTES')
🤓 Hasta los momentos el avance académico, es el siguiente:
{{$course['name']}}, tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
        @if (count($coursesToNotify) > 1)
🚨 Recuerda que para poder certificarte debes aprobar el examen de certificación correspondiente porque no emitimos certificado por haber completado el curso, ni por participación. Y aún cuentas con intentos de examen sin realizar.
        @endif
        @if ($course['lessons_completed'] < $course['lessons_count'])
🙌 Pero *NO TODO ESTÁ PERDIDO,* si no crees certificarte antes que termine tu curso, puedes *EXTENDER el tiempo del curso* y mantener los beneficios que tienes ahora.
Este pago lo debes realizar ahora, ya que la *última semana del curso, las condiciones no serán las mismas y tendrás que ajustarte a los cambios.* Te recuerdo que la fecha fin de tu curso es el día:
        @endif
        @if (($course['status'] == 'SIN INTENTOS GRATIS') && ($course['lessons_completed'] < $course['lessons_count']))
Estos pagos los debes realizar ahora, ya que la *última semana del curso, las condiciones no serán las mismas y tendrás que ajustarte a los cambios.* Te recuerdo que la fecha fin de tu curso es el día:
        @endif
    @endif
@endforeach
{{$endCourseDate->format('d/m/Y')}}

{{-- Cursos SAP anteriores --}}
@if ($showOlderSapCoursesFlag == true)
    @foreach ($olderSapCourses as $course)
Recuerda que antes {{$course['statusToDisplay']}}:    
{{$course['name']}}
    @endforeach
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@if ($showFreeCoursesFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus cursos de obsequio te comento:*
    @foreach ($freeCourses as $course)
        @if ($course['status'] == 'CURSANDO')
Aún estás *cursando:*
        @elseif ($course['status'] == 'REPROBADO')
Completaste pero *REPROBASTE:*
        @elseif ($course['status'] == 'NO CULMINÓ')
*No culminaste:*
        @elseif ($course['status'] == 'ABANDONADO')
*Abandonaste:*
        @elseif ($course['status'] == 'POR HABILITAR')
Aún tienes *por habilitar:*
        @elseif ($course['status'] == 'APROBADO')
*Aprobaste:*
        @endif
{{$course['name']}}        
    @endforeach
@endif

{{-- Advertencia por cursos SAP anteriores --}}
@if ($showWarningSapCourseCertificationFlag == true)
    @if (count($coursesToNotify) > 1)
Por lo que, si no te certificas en este curso SAP:
    @else
Por lo que, si no te certificas en estos cursos SAP:
    @endif
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach

    @foreach ($freeCourses as $course)
        @if ($course['status'] == 'CURSANDO')
A pesar de haberlo iniciado, pierdes el acceso a:
{{$course['name']}}        
        @elseif ($course['status'] == 'APROBADO')
Pierdes el acceso al certificado de:
{{$course['name']}}        
        @elseif ($course['status'] == 'POR HABILITAR')
Y ya no podrás habilitar:
{{$course['name']}}        
        @endif
    @endforeach
@endif

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
Te recuerdo nuevamente que tienes la opción de realizar el pago para extender SAP y/o el ponderado de los exámenes de certificación, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.