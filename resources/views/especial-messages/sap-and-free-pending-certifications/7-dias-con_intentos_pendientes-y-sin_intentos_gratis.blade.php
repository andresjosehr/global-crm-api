{{--

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 7 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 7 dias hacia delante

--}}
{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
💼🚀 *Tu futuro está en juego y así está quedando el avance de tus cursos:* 
@foreach ($coursesToNotify as $course)
({{$course['name']}}), tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
@endforeach

🙌 Si sientes que no podrás certificarte antes de la fecha de vencimiento de los cursos, *recuerda que tienes la solución: EXTENDER LA DURACIÓN DE TU AULA VIRTUAL y mantener los beneficios que tienes actualmente.*
🚩🚩 Sin embargo, *a partir de la llegada de este mensaje, el tiempo mínimo a extender es de 2 meses.* Recuerda que en mensajes anteriores te había comentado que las condiciones iban a cambiar.

🙌 Referente a los cursos: ({{implode(', ', $pendingCoursesToNotifyNames) }}), *no todo está perdido,* porque hemos conseguido una última opción para ti:

@if( $noFreeAttemptsSapCoursesToNotifyCount > 0 )
@endif
@if( $nofreeAttemptsFreeCoursesToNotifyCount == 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tu curso de obsequio, a un precio super especial.
@elseif ( $nofreeAttemptsFreeCoursesToNotifyCount > 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tus cursos de obsequio, a un precio super especial.
@endif

*Responde inmediatamente. Tu futuro está en juego.* 💼🚀 Y la fecha de fin de los cursos es el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. ¡Persiste y alcanza tus metas! 🌟
@else
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. Además, no otorgamos certificados por niveles individuales de Excel. ¡Persiste y alcanza tus metas! 🌟
@endif
No dejes que el tiempo se agote⏳. *Actúa ahora y asegúrate de mantener tu camino hacia la certificación.*


{{-- Cursos SAP anteriores --}}
@if ($showOlderSapCoursesFlag == true)
        @foreach ($olderSapCourses as $course)
Recuerda que antes {{$course['statusToDisplay']}}:
{{$course['name']}}
        @endforeach
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@if ($showOtherFreeCoursesFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus cursos de obsequio te comento:*
        @foreach ($otherFreeCourses as $course)
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
Por lo que, si no te certificas en SAP:
        @foreach ($sapCourses as $course)
{{$course['name']}}
        @endforeach

        @foreach ($otherFreeCourses as $course)
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

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
