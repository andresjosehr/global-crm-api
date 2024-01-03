{{--

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 15 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 15 dias hacia delante

--}}
¡Hola!
{{$student_name}}

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES Y SIN INTENTOS GRATIS --}}
🤓 Te comento sobre el avance académico actual de cada curso:
@foreach ($coursesToNotify as $course)
({{$course['name']}}), tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
@endforeach


🙌 Si sientes que alcanzar la certificación en el tiempo restante es un desafío, *no te desesperes:*
🚩 Tienes la opción de *AMPLIAR el plazo* y conservar los beneficios actuales.

@if( $noFreeAttemptsSapCoursesToNotifyCount > 0 )
🔹 Referente a *SAP,* tienes la posibilidad de pagar por otro intento de examen para obtener tu certificación.
@endif
@if( $nofreeAttemptsFreeCoursesToNotifyCount == 1)
🔹 Referente a *tu curso de obsequio,* puedes pagar para *PONDERAR* los resultados de los exámenes reprobados + el avance académico completado en tu aula virtual.
@elseif ( $nofreeAttemptsFreeCoursesToNotifyCount > 1)
🔹 Referente a *tus cursos de obsequio,* puedes pagar para *PONDERAR* los resultados de los exámenes reprobados + el avance académico completado en tu aula virtual.
@endif

Si estás pensando en esto, *es necesario hacer el pago ahora,* porque a una semana de la fecha de fin, ya no podrás extender por un mes.* Y luego, habrá que seguir otras reglas. ¿Qué dices? ¿Te apuntas y evitamos perder tiempo y el dinero invertido?

📌 Recuerda que estos cursos finalizan el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación.
@else
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación. Tampoco emitimos certificado por nivel independiende de Excel.
@endif

{{-- Cursos SAP anteriores --}}
@if ($showOlderSapCoursesFlag == true)
        @foreach ($olderSapCourses as $course)
Recuerda que antes {{$course['statusToDisplay']}}:
{{$course['name']}}
        @endforeach
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@if ($showOtherFreeCoursesFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus otros cursos de obsequio te comento:*
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
