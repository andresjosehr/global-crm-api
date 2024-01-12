{{--

"PLANTILLAS CURSO SAP CERTIFICADOS  CON CURSOS PENDIENTES
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO CURSO: CERTIFICADO
ESTADO DE EXAMEN: CERTIFICADO
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información del curso:
@else
Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información de los cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy las 23:59, tu aula virtual será eliminada.*
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada.*
@endif

*Sé que te certificaste* 🎓📜 por lo que te comento: 

@if(count($pendingOtherFreeCourses) > 1)
Tienes como *máximo 7 días,* para escoger *una de estas fechas* de inicio:

    @if(count($pendingOtherFreeCourses) == 1)
Para habilitar tu curso:
    @else
Para habilitar tus cursos:
    @endif
    @foreach ($pendingOtherFreeCourses as $course)
    {{$course['name']}}
    @endforeach
@endif

{{-- Filas 56 a 59: Fila 56: cuando sea un curso SAP con estado en la columna de estado PENDIENTE --}}
@if(count($pendingOtherSapCourses) > 0 )
Tienes como *máximo 15 días,* para escoger *una de estas fechas* de inicio:
    @foreach ($pendingOtherSapCourses as $course)
    {{$course['name']}}
    @endforeach
@endif

@if(count($pendingOtherFreeCourses) > 0 || count($pendingOtherSapCourses) > 0)
    @if(count($pendingOtherFreeCourses) == 1 && count($pendingOtherSapCourses) == 1)
    De lo contrario, si no recibimos confirmación de tu parte, lo estarás perdiendo y no podrás recuperarlo luego.
    @else
    De lo contrario, si no recibimos confirmación de tu parte, los estarás perdiendo y no podrás recuperarlos luego.
    @endif

@endif

De ser así, no tendríamos más procedimientos pendientes y cerraremos tu proceso con nosotros.


Quedo atenta si tienes alguna duda y *a tu confirmación de fecha de inicio.*
