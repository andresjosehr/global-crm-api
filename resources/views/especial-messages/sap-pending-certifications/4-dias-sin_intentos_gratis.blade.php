{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 4 DIAS PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 4 dias hacia delante

--}}
@if (count($coursesToNotify) > 1)
¡Urgente, *tu certificación SAP está en peligro!* ⚠️
Tenemos importantes noticias sobre las *condiciones actuales de tu curso:*
@else
¡Urgente, *tus certificaciones SAP están en peligro!* ⚠️
Tenemos importantes noticias sobre las *condiciones actuales de tus cursos:*
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

*Iremos directo al grano para no agobiarte con textos largos:*
{{-- Variante para SIN INTENTOS GRATIS --}}
🚨 Puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual y obtener tu certificado en un máximo de 48 horas hábiles.

🚩 *Si no realizas tu pago hoy, no podremos ayudarte luego y habrás perdido todo.*

Aprovecha que aún tienes posibilidades de salir adelante como Key User SAP y no dar todo por perdido.
Recuerda que si esperas a tu fecha fin:
{{$endCourseDate->format('d/m/Y')}}

{{-- Variante para SIN INTENTOS GRATIS --}}
No podremos ponderar tus resultados fuera de nuestro horario laboral, aunque envíes capturas.

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

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
