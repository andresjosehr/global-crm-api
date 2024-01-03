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

*Sé que te certificaste* 🎓📜 Pero es mi deber informarte.

@if($show6CoursesOffer == true)
También quería saber si te interesaría llevar otro curso de SAP con nosotros,🤩 *con un precio increíble por ser ex alumno.* 🤯
Coméntame y te envío los cursos que tenemos disponibles en este momento, así como las *certificaciones máster* que los acompañan.
@endif


Quedo atenta a tus posibles consultas, de lo contrario sería tu último procedimiento con nosotros porque no tienes más cursos por habilitar.
