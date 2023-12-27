{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$student_name}}

@if ($multipleSapCoursesFlag == false)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($sapCourses as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES --}}
@if ($multipleSapCoursesWithPendingAttemptsFlag == false)
🤓 Hasta los momentos el avance académico de tu curso, es el siguiente:
Tienes ({{$sapCourses[0]['lessons_completed']}}) lecciones completas, y en total son ({{$sapCourses[0]['lessons_count']}}).
@else
🤓 Hasta los momentos, el avance académico de cada curso, es el siguiente:
    @foreach ($sapCourses as $course)
{{$course['name']}}, tiene ({{$course[0]['lessons_completed']}}) lecciones completas, y en total son ({{$course[0]['lessons_count']}}).
    @endforeach
@endif

{{-- Variante para INTENTOS PENDIENTES --}}
@if ($multipleSapCoursesFlag == false)
🚨 Recuerda que para poder certificarte debes aprobar el examen de certificación y aún cuentas con intentos pendientes, porque no emitimos certificado por haber completado el curso, ni por participación.
@else
🚨 Recuerda que para poder certificarte debes aprobar los exámenes de certificación y aún cuentas con intentos pendientes, porque no emitimos certificado por haber completado el curso, ni por participación.
@endif

{{-- ATENCION CORREGIR ACA --}}
🚩 Si no crees que puedas terminar el contenido y aprobar el examen de certificación para el día: // 🚩 Si no crees que puedas aprobar el examen de certificación para el día:
{{-- // ATENCION --}}
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

{{-- Variante para INTENTOS PENDIENTES --}}
Te recuerdo nuevamente que tienes la opción de pagar la extensión de SAP y así certificarte, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.