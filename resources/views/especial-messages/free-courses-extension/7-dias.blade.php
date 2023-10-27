{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳

@php
    $s = count($free_courses) > 1 ? 's' : '';
@endphp

Te saludamos nuevamente del área académica🤓 de *Global Tecnologías Academy,* para comentarte que está por vencer tu{{$s}} curso{{$s}}:

@foreach ($free_courses as $course)
{{$course['name']}}
@endforeach

@if (!in_array(6, array_column($free_courses, 'id')))
Una vez más te indico que para obtener alguna certificación, debes *aprobar el examen correspondiente* y aún cuentas con intentos gratuitos y las condiciones para realizarlos, se encuentran en cada uno de ellos.
@endif

@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) == 1)
Una vez más te indico que para obtener las certificaciones, debes *aprobar los 3 niveles del curso* y aún cuentas con intentos gratuitos y las condiciones para realizarlos, se encuentran en cada uno de ellos.
Recuerda que no brindamos certificado, solo por participación, ni por niveles independientes del curso.
@endif

@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) > 1)
Recuerda que no brindamos certificado, solo por participación, ni por niveles independientes del curso de Excel Empresarial.
@endIf

Si consideras que no podrás culminar y cumplir con las condiciones de certificación, para el día:
{{$end_date}}

Puedes extender tu aula virtual, pero *la información crítica* de la que te hago mención al inicio es la siguiente:
*Ya no es posible extender solo por 1 mes.* Ahora, la extensión mínima es de 2 meses; ya que no realizaste el pago en el momento correspondiente.
Recuerda que esta información fue enviada anteriormente.

No dejes que esta oportunidad escape de tus manos. ¿Deseas extender el plazo y asegurar tu certificación? *Responde inmediatamente. Tu futuro está en juego.* 💼🚀
Si esperas a la fecha de fin, tendrás 1 semana para extender y será *por un mínimo de 3 meses y el aula que recibirás estará completamente vacía, perdiendo así el avance que tenías anteriormente.*
Y pasada esta última semana de plazo, tendrás que volver a matricularte al precio regular.

📌 *RECUERDA* que si en dado caso apruebas algún examen de certificación *antes de la fecha mencionada,* debes indicarme para comentarte los pasos a seguir. Si en dado caso aprobaste y aún no me has indicado, podrías perderlo el día de la fecha de fin de curso.

Aprovecho para recordarte que si deseas recibir el aval internacional del curso de obsequio que apruebes, tendrás que certificarte primero en SAP.

⚠️ Recuerda que el día de tu fecha de fin mencionada líneas arriba, se eliminarán tus accesos de manera automática a las 23:59.
*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:30pm y Sáb. 9:00am a 6:00pm.*
