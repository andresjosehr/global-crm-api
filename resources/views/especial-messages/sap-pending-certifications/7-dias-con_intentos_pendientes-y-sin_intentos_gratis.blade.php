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

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
💼🚀 *Tu futuro está en juego:* 
*Este es el estado actual de tus cursos SAP habilitados:*
@foreach ($sapCourses as $course)
{{$course['name']}}
    @if ($course['status'] == 'SIN INTENTOS GRATIS')
🚨 Completaste, pero no aprobaste el examen, con los 3 intentos que te ofrecimos de manera gratuita.
🙌 Te comento que *no todo está perdido,* porque hemos conseguido una solución adicional para ti:
🚨 Puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual.
*Posterior a tu pago, en máximo 48 horas hábiles tendrás el certificado y la insignia digital respaldada por Credly.*
📌 *¡No pierdas más tiempo y realiza el pago en este momento!* Ya que, si esperas a los próximos días, perderás esta posibilidad porque tu aula se elimina el día:
    @elseif ($course['status'] == 'INTENTOS PENDIENTES')
💼🚀 *Así está tu avance académico:* 
{{$course['name']}}, tiene (CANTIDAD DE LECCIONES COMPLETAS EN EL AULA) lecciones completas, y en total son (TOTAL DE LECCIONES DEPENDIENDO DEL CURSO).
Y tus cursos finalizan el día:
    @endif
@endforeach
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

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
💭 Piensa la opción de realizar el pago para extender SAP y/o el ponderado de los exámenes de certificación, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.