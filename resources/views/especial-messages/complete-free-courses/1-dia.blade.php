¡Hola!
{{$student_name}}

@php
$freeCoursesCount = count(array_filter($student['courses'], function($course) {
    return $course['type'] == 'free';
}));
$s = $freeCoursesCount > 2 ? 's' : '';
@endphp

Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información del curso{{$s}}:
Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información @php echo $s == 's' ? 'de los' : 'del' @endphp cursos{{$s}}:
CURSO

🚨 *Hoy a las 23:59, tu aula virtual será eliminada.*
🚨 *Mañana a las 23:59, tu aula virtual será eliminada.*
Sé que te certificaste en todos los cursos, pero es mi deber informarte que ya ha culminado el plazo de tu aula virtual.
Sé que te certificaste, pero es mi deber informarte que ya ha culminado el plazo de tu aula virtual.

⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que el certificado por este curso aprobado, lo recibirás cuando te certifiques en SAP.
⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que los certificados por estos cursos aprobados, los recibirás cuando te certifiques en SAP.
⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que los certificados por este curso aprobado, los recibirás cuando te certifiques en SAP.
