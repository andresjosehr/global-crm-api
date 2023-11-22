@php
    $sapCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'paid';
    });
    $sapCourse = array_values($sapCourse)[0];
@endphp

Lamentamos no contar con tu participación en la certificación como KEY USER SAP para el curso {{$sapCourse['name']}}.

En este caso, como *estás abandonando el curso principal ({{$sapCourse['name']}}),* te comento lo siguiente sobre tus cursos de obsequio:

{{-- Foreach --}}
@php
    $freeCourses = array_filter($student['courses'], function($course) {
        return $course['type'] == 'free';
    });
    $freeCourses = array_values($freeCourses);
@endphp

Aún estás *cursando:*
CURSO
Completaste pero *REPROBASTE:*
CURSO
*No culminaste:*
CURSO
*Abandonaste:*
CURSO
Aún tienes *por habilitar:*
CURSO
*Aprobaste:*
CURSO

Por lo que, al abandonar el curso principal, que es SAP:
Automáticamente pierdes el acceso a este curso, pesar de haberlo iniciado:
CURSO
Pierdes el acceso al certificado de:
CURSO
Y ya no podrás habilitar:
CURSO

Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula.

Saludos.
