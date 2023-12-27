{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 7 días PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 7 días hacia delante

--}}
{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳


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
💼🚀 *Tu futuro está en juego y así está quedando el avance de tu curso:*
Tienes ({{$sapCourses[0]['lessons_completed']}}) lecciones completas, y en total son ({{$sapCourses[0]['lessons_count']}}).
@else
💼🚀 *Tu futuro está en juego y así está quedando el avance de cada curso:* 
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

🙌 Aún *tienes una solución en tus manos,* todavía puedes extender el curso, solo que *ya no puedes hacerlo por 1 mes.*
*El tiempo mínimo de extensión en este momento, es por 2 meses.* Recuerda que esta información crítica fue enviada anteriormente.

No dejes que esta oportunidad escape de tus manos. ¿Deseas extender el plazo y asegurar tu certificación? Responde inmediatamente. 

Si esperas a que finalice tu curso, tendrás 1 semana para extender y será *por un mínimo de 3 meses y el aula que recibirás estará completamente vacía, perdiendo así el avance que tenías anteriormente.* 
Y pasada esta última semana de plazo, tendrás que volver a matricularte al precio regular del curso. 

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
💭 Piensa en la opción de pagar la extensión de SAP y así certificarte, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.