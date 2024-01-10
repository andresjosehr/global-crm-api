{{--

"PLANTILLAS CURSO SAP CERTIFICADOS  CON CURSOS PENDIENTES
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO CURSO: CERTIFICADO
ESTADO DE EXAMEN: CERTIFICADO
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Quería recordarte que ya se acerca el término del tiempo brindado para llevar tu curso:
@else
Quería recordarte que ya se acerca el término del tiempo brindado para llevar tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

Es decir que tendrás acceso al contenido del curso SAP y al software, *hasta el día:*
{{$endCourseDate->format('d/m/Y')}}

@if(count($pendingOtherFreeCourses) > 1)
*Sé que te certificaste* 🎓📜 así que quería consultarte si ya deseas iniciar con: 
{{implode(', ', array_column($pendingOtherFreeCourses, "NAME"))  }}
@endif

@if($otherFreeCourseInProgressOrCompletedCount >0)

    @if($otherFreeCourseInProgressOrCompletedCount == 1)
    *Sé que te certificaste* 🎓📜 así que  te recuerdo el estado de tu curso:
   @else
   *Sé que te certificaste* 🎓📜 así que  te recuerdo el estado de los demás cursos:
    @endif

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'CURSANDO')
            Aún estás *cursando:*
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'REPROBADO')
        Completaste pero *REPROBASTE:*
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'NO CULMINÓ')
        *No culminaste:*
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'ABANDONÓ')
        *Abandonaste:*
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'POR HABILITAR')
            Aún tienes *por habilitar:*
            {{$course['name']}}
            Por favor me indicas si *deseas iniciar de una vez,* para enviarte las *fechas disponibles.*

        @elseif ($course['course_status_original'] == 'APROBADO')
        *Aprobaste:*
        {{$course['name']}}
        @endif
    @endforeach
@endif


@if($show6CoursesOffer == true)
También quería saber si te interesaría llevar otro curso de SAP con nosotros,🤩 *con un precio increíble por ser ex alumno.* 🤯
Coméntame y te envío los cursos que tenemos disponibles en este momento, así como las *certificaciones máster* que los acompañan.
@endif

{{-- Fila 35: solo si en las columnas de los nombres de los cursos de obsequio, se encuentra el estado NO APLICA --}}
@if($showOtherFreeCourseOffer == true)
También tenemos disponible *el paquete Office: Excel Empresarial 3 niveles, Power BI y MS Project,* que también podría interesarte.
@elseif($showSecondChanceOtherFreeCourseOffer == true)
También tenemos disponible *el paquete Office: Excel Empresarial 3 niveles, Power BI y MS Project,* que también podría interesarte, ya que no lograste certificarte.
@endif


Quedo atenta a tus posibles consultas y a que me indiques si deseas que te envíe de una vez las *fechas de inicio de los cursos de obsequio.*

