¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:
{{$student_name}}

@php
    $s = count($free_courses) > 1 ? 's' : '';
@endphp

Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información @php echo $s ? 'los' : 'el' @endphp curso{{$s}}:
{{-- foreach --}}
@foreach ($free_courses as $course)
{{$course['name']}}
@endforeach
{{-- endforeach --}}

🚨 *A las 23:59, tu aula virtual será eliminada,* es decir que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
Asimismo, veo que no realizaste todos los intentos de examen de certificación, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación.

Si en dado caso realizas los intentos de examen fuera de mi horario laboral, no aceptaremos capturas de pantalla de tu aprobación para apoyarte con el certificado, *sin excepciones.*

⏳ *¡Actúa ya!* Paga 3 meses de aula virtual de todos los cursos HOY o como máximo en 1 semana.
De igual manera te comento que al realizar el pago, el aula virtual que recibirás estará completamente vacía porque tu avance se habría eliminado.

@if(count($free_courses) == 1)
Si por el contrario, deseas retomar el curso *pasada la semana indicada, tendrás que volver a matricularte con el precio regular.* Recordando que este curso te los ofrecimos como obsequio.
@endif

@if(count($free_courses) > 1)
Si por el contrario, deseas retomar algún curso *pasada la semana indicada, tendrás que volver a matricularte con el precio regular.* Recordando que estos cursos te los ofrecimos como obsequio.
@endif

⚠️ *Importante: Pagos fuera de mi horario laboral no serán reconocidos. No habrá reembolsos, tendrá que completar el valor faltante de ser el caso.*

*Ha sido una lástima no contar con tu participación en esta certificación.*

@if($include_sap)
Nos mantendremos en contacto, referente a tu curso de SAP, ya que como te comenté si no extiendes los cursos de obsequio, los habrías perdido.
@endif
