⚠️ ¡Atención urgente! ⏳
{{$student_name}}

@php
    $s = count($free_courses) > 1 ? 's' : '';
@endphp

Te saludamos nuevamente del área académica🤓 de *Global Tecnologías Academy,* para comentarte que está por vencer tu{{$s}} curso{{$s}}:

{{-- foreach --}}
@foreach ($free_courses as $course)
{{$course['name']}}
@endforeach
{{-- endforeach --}}

Recuerda que para poder certificarte, debes *aprobar el examen correspondiente,* de lo contrario, no obtendrás ningún certificado, porque *no brindamos certificado por participación.*

Tu fecha de fin es el día:
{{$end_date}}

Si sientes que no podrás completar todo el contenido y cumplir con la condición para la certificación antes de la fecha de vencimiento, *estamos aquí para ofrecerte una solución.*
*Puedes extender la duración de tu aula virtual y mantener todos los beneficios que disfrutas actualmente.*

🚨 Sin embargo, *esta oportunidad solo está disponible si realizas el pago de la extensión en este momento,* ya que a partir de la siguiente semana, las condiciones de extensión serán otras.
No dejes que el tiempo se agote. Si esperas hasta una semana antes de tu fecha de fin, tendrás que extender como mínimo por 2 meses.*No será posible extender por 1 solo mes.*
*Actúa ahora y asegúrate de mantener tu aula virtual activa y tu camino hacia la certificación.*

📌 *RECUERDA* que si en dado caso apruebas algún examen de certificación *antes de la fecha mencionada,* debes indicarme para comentarte los pasos a seguir. Si en dado caso aprobaste y aún no me has indicado, lo podrías perder el día de la fecha de fin antes mencionada.

@if (in_array(6, array_column($free_courses, 'id')))
👀 OJO: recuerda que *Excel Empresarial tiene la siguiente condición para ser certificado:*
Debes aprobar los *3 niveles* para poder obtener los 3 certificados, ya que no brindamos certificado por niveles independientes.
@endif

@if($include_sap)
Aprovecho para recordarte que si deseas recibir el aval internacional del curso de obsequio que apruebes, tendrás que certificarte primero en SAP.
@endif


⚠️ Recuerda que el día de tu fecha de fin mencionada líneas arriba, se eliminarán tus accesos de manera automática a las 23:59.
*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:30pm y Sáb. 9:00am a 6:00pm.*
