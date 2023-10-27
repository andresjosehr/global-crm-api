¡Hola!
{{$student_name}}

@php
    $s = count($free_courses) > 1 ? 's' : '';
@endphp

Te saludamos del área académica🤓 de *Global Tecnologías Academy,* para comentarte que está por vencer tu{{$s}} curso{{$s}}:
{{-- foreach --}}
@foreach ($free_courses as $course)
{{$course['name']}}
@endforeach
{{-- endforeach --}}

🚨 Te recuerdo que para poder obtener algún certificado, debes *aprobar* el examen correspondiente; ya que no brindamos certificado por participación.

{{-- Solamente Powerbi y msproject --}}
@if (!in_array(6, array_column($free_courses, 'id')) && count($free_courses) == 1)
    En tu aula virtual encontrarás el primer intento de examen gratuito, sin embargo aplicarán condiciones para poder realizarlo. _Las mismas se encuentran antes de iniciarlo._ Por lo que te recomiendo leer bien, antes de aceptar.
@endif

{{-- Solamente Excel --}}
@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) == 1)
    En tu aula virtual encontrarás el primer intento de examen gratuito por cada nivel, sin embargo aplicarán condiciones para poder realizarlo. _Las mismas se encuentran antes de iniciarlo._ Por lo que te recomiendo leer bien, antes de aceptar.
@endif


@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) > 1)
En tu aula virtual encontrarás el primer intento de examen gratuito por cada curso y nivel, sin embargo aplicarán condiciones para poder realizarlo. _Las mismas se encuentran antes de iniciarlo._ Por lo que te recomiendo leer bien, antes de aceptar.
@endif

{{-- Solamente Powerbi y msproject --}}
@if (!in_array(6, array_column($free_courses, 'id')))
    Si repruebas el primer intento, solo se habilitará el segundo y tercero, si has completado todo el contenido. *Te recuerdo que solo son 3 intentos gratuitos por cada curso.*
@endif

{{-- Solamente Excel --}}
@if (in_array(6, array_column($free_courses, 'id')) && count($free_courses) == 1)
    Si repruebas el primer intento, solo se habilitará el segundo y tercero, si has completado todo el contenido. *Te recuerdo que solo son 3 intentos gratuitos por cada nivel.*
@endif

{{-- Si los cursos terminan el mismo dia y contiene excel --}}
@if (count($free_courses) > 1 && in_array(6, array_column($free_courses, 'id')))
Si repruebas el primer intento, solo se habilitará el segundo y tercero, si has completado todo el contenido. *Te recuerdo que solo son 3 intentos gratuitos por cada curso y nivel.*
@endif

Si no crees que puedas terminar y cumplir con los requisitos para certificarte, antes de la fecha de fin, *puedes extender el tiempo de tu aula virtual* y mantener los beneficios que tienes ahora.
Por favor me indicas si deseas hacerlo y no perder la oportunidad de certificarte, ya que para ello debemos hacer un trámite administrativo previo y se debe realizar en estas fechas.
*Si esperas a la última semana para realizar el pago, perderás la oportunidad de extender solo por 1 mes.* Y tendrás que ajustarte a las nuevas condiciones de extensión.

Por lo tanto, quería saber si lograrás cumplir con los requisitos de certificación, para el:
{{$end_date}}

📌 *RECUERDA* que si en dado caso apruebas algún examen de certificación *antes de la fecha mencionada,* debes indicarme para comentarte los pasos a seguir. Si en dado caso aprobaste y aún no me has indicado, lo podrías perder el día de la fecha de fin antes mencionada.

{{-- Solamente excel --}}
@if (in_array(6, array_column($free_courses, 'id')))
👀 OJO: recuerda que *Excel Empresarial tiene la siguiente condición para ser certificado:*
Debes aprobar los *3 niveles* para poder obtener los 3 certificados, ya que no brindamos certificado por niveles independientes.
@endif

@if($include_sap)
Aprovecho para recordarte que si deseas recibir el aval internacional del curso de obsequio que apruebes, tendrás que certificarte primero en SAP.
@endif

⚠️ Recuerda que el día de tu fecha de fin mencionada líneas arriba, se eliminarán tus accesos de manera automática a las 23:59.
*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:30pm y Sáb. 9:00am a 6:00pm.*

Quedo al pendiente de tu respuesta y si necesitas alguna ayuda o que te brindemos opciones.
