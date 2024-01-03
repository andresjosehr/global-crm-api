{{--

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$student_name}}

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES --}}
🤓 Te comento sobre el avance académico que tienes actualmente en cada curso:
@foreach ($coursesToNotify as $course)
({{$course['name']}}), tiene ({{$course['lessons_completed']}}) lecciones completas, y en total son ({{$course['lessons_count']}}).
@endforeach


{{-- Variante para INTENTOS PENDIENTES --}}
🙌 Si no crees poder certificarte en el tiempo que te queda disponible, *NO TODO ESTÁ PERDIDO:*
🚩 Puedes *EXTENDER el tiempo* y mantener los beneficios que tienes ahora.

Si te interesa tomar esta opción, *te recomiendo realizar el pago en este momento,* ya que, *una semana antes de la fecha de fin,* no estará disponible la extensión de 1 mes.* Y tendrás que ajustarte a las nuevas condiciones de extensión.
Por favor me indicas si te interesa *y no perder el tiempo y el dinero que has invertido.*

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