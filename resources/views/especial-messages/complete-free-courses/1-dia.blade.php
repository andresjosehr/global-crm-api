¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:
{{$student['NOMBRE']}}

@php
    $freeCourcesToExpire = array_filter($student['CURSOS'], function ($course) {
        return $course['diff'] == 1 && $course['type'] == 'free' && $course['type'] == 'certifaction_test_original' && ($course['course_status_original'] == 'COMPLETA' || $course['course_status_original'] == 'COMPLETA SIN CREDLY');
    });
    $s = count($freeCourcesToExpire) > 1 ? 's' : '';
    $isSaturday = date('N') == 6;
@endphp

Te envío la última información de tu{{$s}} curso{{$s}}: {{-- Cuando es un solo curso que tiene diff = 1 --}}
CURSO

🚨 *Hoy a las 23:59, tu aula virtual será eliminada,* es decir que se perderán todos los avances realizados, no pudiendo ser recuperados luego. {{-- Si la fecha end del curso es un dia distinto a domingo --}}
🚨 *Mañana a las 23:59, tu aula virtual será eliminada,* es decir que se perderán todos los avances realizados, no pudiendo ser recuperados luego.  {{-- Si la fecha end del curso es domingo --}}
Asimismo, no tendremos respaldo, ni aceptaremos capturas de pantalla de las notas obtenidas, para poder solicitar el ponderado, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación. {{-- Cuando el/los curso(s) con diff = 1 no incluya un curso con course_id = 6 --}}
Asimismo, no tendremos respaldo, ni aceptaremos capturas de pantalla de las notas obtenidas, para poder solicitar el ponderado, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación, ni por niveles independientes. {{-- Cuando el/los curso(s) con diff = 1 incluya un curso con course_id = 6 --}}

Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado.* {{-- Cuando el/los curso(s) con diff = 1 y certifaction_test_original = Reprobado solo sea uno  --}}
Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado.* {{-- Cuando el/los curso(s) con diff = 1 y certifaction_test_original = Reprobado sean varios  --}}
Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado,* porque culminaste con cada nivel de la siguiente manera:
NIVEL BÁSICO ESTADO
NIVEL INTERMEDIO ESTADO
NIVEL AVANZADO ESTADO
Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado,* porque con Excel culminaste con cada nivel de la siguiente manera:
NIVEL BÁSICO ESTADO
NIVEL INTERMEDIO ESTADO
NIVEL AVANZADO ESTADO
Es decir, que *aunque hayas aprobado ese nivel, no recibirás certificación alguna porque la condición para certificar Excel Empresarial, es que hayas aprobado todos los niveles que lo comprenden.*

Por lo que, al tener ((X)) cursos reprobados, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:
Como aún no te certificas en SAP, al haber reprobado solo un curso, aún mantienes el acceso a:
Al no haberte certificado en SAP y tener ((X)) cursos reprobados, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:
Al no haberte certificado en SAP y tener este curso reprobado, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:
CURSO


Por lo que, al haber reprobado SAP y también:
CURSO

A pesar de quedar pendiente, no podrás habilitar:
CURSO
A pesar de haber iniciado, pierdes el acceso a:
CURSO
A pesar de haber aprobado, pierdes el acceso al certificado internacional:
CURSO
Ya que tendrías ((X)) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*

Como aún no te certificas en SAP, al haber reprobado solo un curso:
Puedes habilitar:
CURSO
Solo que, para iniciar este curso, esperaremos a que apruebes SAP para poder habilitarlo.
Solo que, para iniciar estos cursos, esperaremos a que apruebes SAP para poder habilitarlos.
Aún mantienes el acceso a:
CURSO
Cuando recibas tu certificado en SAP, podrás recibir también el certificado con aval internacional de:
CURSO


Como aún no te certificas en SAP, al haber reprobado estos ((X)) cursos:
No podrás habilitar:
CURSO
Pierdes el acceso a:
CURSO
Pierdes el acceso al certificado internacional:
CURSO
Ya que tendrías ((X)) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*


Como aún no te certificas en SAP, reprobaste dos cursos y no culminaste:
Como aún no te certificas en SAP, reprobaste un curso y no culminaste:
Por lo que, como también reprobaste SAP y no culminaste con:
CURSO

No puedes habilitar:
CURSO
Pierdes el acceso a:
CURSO
No tendrás el certificado internacional:
CURSO

Ya que tendrías ((X)) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
Ya que tendrías ((X)) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*


Como aún no te certificas en SAP, reprobaste dos cursos y abandonaste:
Como aún no te certificas en SAP, reprobaste un curso y abandonaste:
Por lo que, como también reprobaste SAP y no culminaste con:
CURSO

No puedes habilitar:
CURSO
Pierdes el acceso a:
CURSO
No tendrás el certificado internacional:
CURSO

Ya que tendrías ((X)) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
Ya que tendrías ((X)) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*


Como aún no te certificas en SAP y reprobaste dos cursos:
Como aún no te certificas en SAP y reprobaste un curso:
Por lo que, como también reprobaste SAP:
No podrás habilitar:
CURSO

Pierdes el acceso a:
CURSO
Pierdes el certificado internacional:
CURSO

Ya que tendrías ((X)) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
Ya que tendrías ((X)) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*

🚩 🚩 *¡AÚN ES POSIBLE LOGRAR QUE TE CERTIFIQUES!* No pierdas lo que ya has logrado.
⏳ *¡Actúa ya!* Paga HOY con un precio especial el ponderado, ¡no pierdas esta oportunidad!
*Eso sí, el pago debe ser dentro de mi horario laboral.*

Si, por el contrario, deseas realizar tu pago mañana u otro día, tendrás que matricularte con el precio regular de este curso.* Ya que te recuerdo, que esto fue un obsequio por haberte matriculado en SAP.
Si, por el contrario, deseas realizar tu pago mañana u otro día, tendrás que rematricularte con el precio regular de este curso.

⚠️ *Importante: Pagos fuera de mi horario laboral no serán reconocidos. No habrá reembolsos, tendrás que completar el valor para rematrícula.*

*Ha sido una lástima no contar con tu participación en esta certificación.*
*Ha sido una lástima no contar con tu participación en estas certificaciones.*
