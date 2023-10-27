@php
    $s = count($free_courses) > 1 ? 's' : '';
@endphp

¡Urgente, tu{{$s}} curso{{$s}} está en peligro! ⚠️

Es un gusto saludarte una vez más, desde el área académica de *Global Tecnologías Academy* 🤓.
Tenemos importantes noticias sobre las condiciones de extensión de:
{{-- foreach --}}
@foreach ($free_courses as $course)
{{$course['name']}}
@endforeach
{{-- endforeach --}}

Siendo tu fecha de fin, el día:
{{$end_date}}

@if (!in_array(6, array_column($free_courses, 'id')))
*Recuerda:* en tu aula virtual, aún cuentas con la posibilidad de realizar los intentos de examen de certificación, de forma gratuita. Las condiciones para realizarlos, se encuentran en ella.
@endif

@if(in_array(6, array_column($free_courses, 'id')) && count($free_courses) == 1)
*Recuerda*: las condiciones para realizar el examen de certificación de cada nivel de *Excel Empresarial* se encuentran en tu aula virtual, recuerda que debes aprobarlos ya que de lo contrario, no obtendrás ningún certificado, porque *no brindamos certificados por participación.*
@endif

@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) > 1)
*Recuerda*: las condiciones para realizar cada examen de certificación, dependiendo del curso, se encuentran en tu aula virtual, recuerda que debes aprobarlos ya que de lo contrario, no obtendrás ningún certificado, porque no brindamos certificados por participación.*
@endif

Sin embargo, si no sientes la seguridad de poder cumplir con las condiciones para certificarte, *te recomendamos encarecidamente que consideres la opción de extender el tiempo de tu aula virtual.*

🚨 Queremos recordarte que desde hace unos días, *la extensión mínima disponible es de 2 meses.*

⚠️ Te informamos que si decides esperar hasta el último día para realizar el pago de la extensión, deberás hacerlo dentro de mi horario laboral, ya que el sistema elimina automáticamente tus accesos a las 23:59 horas.

Esto significa que no se conservará ningún respaldo de tu progreso y lamentablemente no podremos aceptar capturas de pantalla como prueba para apoyarte con algún certificado.
*Si en dado caso decides realizar el pago fuera de mi horario laboral, el mismo no será reconocido y tu aula será eliminada porque no habrá quien reporte tu pago:*

*Teniendo así que ajustarte a las condiciones de extensión mencionadas anteriormente completando el valor faltante, ya que no realizaremos ninguna devolución por el pago realizado.*

📌 *RECUERDA* que si en dado caso apruebas algún examen de certificación *antes de la fecha mencionada,* debes indicarme para comentarte los pasos a seguir. Si en dado caso aprobaste y aún no me has indicado, lo perderías el día de la fecha de fin de curso.

@if (in_array(6, array_column($free_courses, 'id')))
👀 OJO: recuerda que *Excel Empresarial tiene la siguiente condición para ser certificado:*
Debes aprobar los *3 niveles* para poder obtener los 3 certificados, ya que no brindamos certificado por niveles independientes.
@endif

Aprovecho para recordarte que si deseas recibir el aval internacional del curso de obsequio que apruebes, tendrás que certificarte primero en SAP.

⚠️ Recuerda que el día de tu fecha de fin mencionada líneas arriba, se eliminarán tus accesos de manera automática a las 23:59.
