{{--

"PLANTILLAS CURSO CON EXTENSIONES
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP u OBSEQUIO
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$studentData['NOMBRE']}}

Te saludamos del área académica🤓 de Global Tecnologías Academy, para comentarte que el proceso administrativo para la extensión de:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach
Se ha realizado con éxito.

Aprovecho para recordarte que tu nueva fecha de fin, está pautada para el:
{{$endCourseDate->format('d/m/Y')}}

Saludos cordiales.
