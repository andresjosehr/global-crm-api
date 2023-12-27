{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
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

{{-- Variante para SIN INTENTOS GRATIS --}}
🚨 Completaste el curso, pero lamentablemente agotaste todos los intentos de examen de certificación que te ofrecimos de manera gratuita.

🙌 *Pero NO TODO ESTÁ PERDIDO,* puedes *pagar por un intento adicional de examen* y así certificarte, recuerda que no brindamos certificado por participación, ni por haber completado el curso.
🚩 Este pago lo debes hacer en este momento, para poder realizar los trámites necesarios y que puedas realizar el nuevo intento, *en un plazo no mayor a 48 horas hábiles.* Ya que tu fecha de fin es el día:
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

{{-- Variante para SIN INTENTOS GRATIS --}}
Te recuerdo nuevamente que tienes la opción de pagar el ponderado de tus exámenes de SAP y así certificarte, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.