{{--

"PLANTILLAS CURSO SAP SIN INTENTOS GRATIS"
FALTANDO 7 días PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 7 días hacia delante

--}}
{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳


@if (count($coursesToNotify) > 1)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para SIN INTENTOS GRATIS --}}
*Tu futuro está en juego y no hemos concretado tu pago por el intento adicional de examen de certificación.* 💼🚀
🙌 Te comento que *no todo está perdido,* porque hemos conseguido una solución adicional para ti:
🚨 Puedes pagar para *PONDERAR* los resultados de tus exámenes + el avance académico completado en tu aula virtual.
*Posterior a tu pago, en máximo 48 horas hábiles tendrás el certificado y la insignia digital respaldada por Credly.*
📌 *¡No pierdas más tiempo y realiza el pago en este momento!* Ya que, si esperas a los próximos días, perderás esta posibilidad porque tu aula se elimina el día:
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

{{-- Variante para SIN INTENTOS GRATIS --}}
💭 Piensa en la opción de pagar el ponderado de tus exámenes de SAP y así certificarte, para no perder el acceso a tus cursos de obsequio.

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.