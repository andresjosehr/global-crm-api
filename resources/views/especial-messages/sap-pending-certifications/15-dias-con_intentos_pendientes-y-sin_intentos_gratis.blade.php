{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 15 DIAS PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 15 días hacia delante

--}}
⚠️ ¡Atención urgente! ⏳

{{$student_name}}

@if ($multipleSapCoursesFlag == false)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($sapCourses as $course)
{{$course['name']}}
@endforeach

Es crucial que *tomes acción de inmediato para asegurar tu certificación SAP,* ya que recuerda que no emitimos certificado por participación, ni por completar algún curso.

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
*Este es el estado actual de tus cursos SAP habilitados:*
@foreach ($sapCourses as $course)
{{$course['name']}}
    @if ($course['status'] == 'SIN INTENTOS GRATIS')
🚨 Completaste el curso, pero no aprobaste el examen con los 3 intentos que te ofrecimos gratuitamente.
🙌 *Pero NO TODO ESTÁ PERDIDO,* puedes *pagar por un intento adicional de examen* y así certificarte.
    @elseif ($course['status'] == 'INTENTOS PENDIENTES')
🤓 Este es tu avance académico:
{{$course['name']}}, tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
        @if ($multipleSapCoursesFlag == false)
🚨 Recuerda que para poder certificarte debes aprobar el examen de certificación correspondiente. Y aún cuentas con intentos de examen sin realizar.
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

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
🚩 Este es el estado actual de tus cursos SAP habilitados:
@foreach ($sapCourses as $course)
{{$course['name']}}
@endforeach
🚨 Completaste, pero no aprobaste el examen, con los 3 intentos que te ofrecimos de manera gratuita.
🙌 *Pero NO TODO ESTÁ PERDIDO,* puedes *pagar por un intento adicional de examen* y así certificarte,
🤓 Este es tu avance académico:
@foreach ($sapCourses as $course)
{{$course['name']}}, tiene (CANTIDAD DE LECCIONES COMPLETAS EN EL AULA) lecciones completas, y en total son (TOTAL DE LECCIONES DEPENDIENDO DEL CURSO).
Tienes ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
@endforeach
@if ($multipleSapCoursesFlag == false)
🚨 Recuerda que para poder certificarte debes aprobar el examen de certificación correspondiente. Y aún cuentas con intentos de examen sin realizar.
@endif
{{-- ATENCION CORREGIR ACA --}}
🙌 Pero *NO TODO ESTÁ PERDIDO,* si no crees certificarte antes que termine tu curso, puedes *EXTENDER el tiempo del curso* y mantener los beneficios que tienes ahora.

Este pago lo debes realizar ahora, ya que la *última semana del curso, las condiciones no serán las mismas y tendrás que ajustarte a los cambios.* Te recuerdo que la fecha fin de tu curso es el día:
Estos pagos los debes realizar ahora, ya que la *última semana del curso, las condiciones no serán las mismas y tendrás que ajustarte a los cambios.* Te recuerdo que la fecha fin de tu curso es el día:
{{-- /ATENCION CORREGIR ACA --}}
{{$endCourseDate->format('d/m/Y')}}

{{-- Variante para INTENTOS PENDIENTES --}}
@if ($multipleSapCoursesFlag == false)
🙌 *NO TODO ESTÁ PERDIDO,* puedes *EXTENDER el tiempo del curso* y mantener los beneficios que tienes ahora.
@else
🙌 *NO TODO ESTÁ PERDIDO,* puedes *EXTENDER el tiempo de los cursos* y mantener los beneficios que tienes ahora.
@endif

{{-- Variante para INTENTOS PENDIENTES --}}
*Te recomiendo realizar el pago en este momento,* ya que la *última semana del curso, no está disponible la extensión de 1 mes.* Y tendrás que ajustarte a las nuevas condiciones de extensión.
Por favor me indicas si te interesa tomar esta opción *y no perder el tiempo y el dinero que has invertido.*

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
    @if ($multipleSapCoursesFlag == false)
Por lo que, si no te certificas en este curso SAP:
    @else
Por lo que, si no te certificas en estos cursos SAP:
    @endif
    @foreach ($sapCourses as $course)
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