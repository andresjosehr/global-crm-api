@php
// cache interna
$otherFreeCoursesInProgressNames = [];
$otherFreeCoursesDissaprovedNames = [];
$otherFreeCoursesDroppedNames = [];
$otherFreeCoursesUnfinishedNames = [];
$otherFreeCoursesApprovedNames = [];
$otherFreeCoursesToEnableNames = [];
foreach($otherFreeCourses as $course):

   switch ($course['course_status']) {
        case 'CURSANDO':
            $otherFreeCoursesInProgressNames[] = $course['name'];
            break;
        case 'REPROBADO':
            $otherFreeCoursesDissaprovedNames[] = $course['name'];
            break;
        case 'ABANDONADO':
            $otherFreeCoursesDroppedNames[] = $course['name'];
            break;
        case 'NO CULMINÓ':
            $otherFreeCoursesUnfinishedNames[] = $course['name'];
            break;
        case 'APROBADO':
            $otherFreeCoursesApprovedNames[] = $course['name'];
            break;
        case 'POR HABILITAR':
            $otherFreeCoursesToEnableNames[] = $course['name'];
            break;
    }
endforeach;


// cache interna
$otherSapCoursesInProgressNames = [];
$otherSapCoursesDissaprovedNames = [];
$otherSapCoursesDroppedNames = [];
$otherSapCoursesUnfinishedNames = [];
$otherSapCoursesApprovedNames = [];
$otherSapCoursesToEnableNames = [];
$otherSapCoursesCertifiedNames = [];
foreach($otherSapCourses as $course):

   switch ($course['course_status']) {
        case 'CURSANDO':
            $otherSapCoursesInProgressNames[] = $course['name'];
            break;
        case 'REPROBADO':
            $otherSapCoursesDissaprovedNames[] = $course['name'];
            break;
        case 'ABANDONADO':
            $otherSapCoursesDroppedNames[] = $course['name'];
            break;
        case 'NO CULMINÓ':
            $otherSapCoursesUnfinishedNames[] = $course['name'];
            break;
        case 'APROBADO':
            $otherSapCoursesApprovedNames[] = $course['name'];
            break;
            case 'POR HABILITAR':
            $otherSapCoursesToEnableNames[] = $course['name'];
            break;
            case 'CERTIFICADO':
            $otherSapCoursesCertifiedNames[] = $course['name'];
            break;
    }
endforeach;

$coursesToNotifyNames = array_column($coursesToNotify, 'name');
$sapCoursesNames = array_column($sapCourses, 'name');


@endphp
{{--

"PLANTILLAS CURSO OBSEQUIOS CURSANDO CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: FREE
ESTADO CURSO: CURSANDO
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Te envío la última información de tu curso:

@else
Te envío la última información de tus cursos:

@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy a las 23:59, tu aula virtual será eliminada,* es decir que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada,* es decir que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@endif

{{-- VAriante solo Cursos PBI o MSP --}}
@if ($hasExcelCourseToNotify == false)
Asimismo, no tendremos respaldo, ni aceptaremos capturas de pantalla de las notas obtenidas, para poder solicitar el ponderado, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación.
@else
Asimismo, no tendremos respaldo, ni aceptaremos capturas de pantalla de las notas obtenidas, para poder solicitar el ponderado, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación, ni por niveles independientes.
@endif

{{-- VAriante solo Cursos PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
    Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado.*
    @else
    Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado.*
    @endif
@endif

{{-- VAriante para Cursos PBI o MSP, Y EXCEL --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
Y a la hora que te envío este mensaje, el estado de tu curso es *reprobado,* porque culminaste con cada nivel de la siguiente manera:
    @foreach ($coursesToNotify as $course)
    @if ($course['isExcelCourse'] == true)    
        @foreach($course['LEVELS'] as $level)
            @if ($course[$level]['noFreeAttempts'] == true)
                El nivel {{$course[$level]['name']}}: reprobado.
            @endif
        @endforeach
    @endif
@endforeach

@endif


{{-- VAriante solo Cursos EXCEL --}}
@if ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
Y a la hora que te envío este mensaje, el estado de tus cursos es *reprobado,* porque con Excel culminaste con cada nivel de la siguiente manera:

    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == true)
            @foreach($course['LEVELS'] as $level)
                @if ($course[$level]['noFreeAttempts'] == true)
                    El nivel {{$course[$level]['name']}}: reprobado.
                @endif
            @endforeach
        @endif
    @endforeach
@endif

{{-- Fila 654: Cuando sea Excel uno de los cursos que termina y tenga un nivel aprobado --}}
@php
$tmpExcelApprovedFlag = false;
foreach ($coursesToNotify as $course):
    if ($course['isExcelCourse'] == true):
            foreach($course['LEVELS'] as $level):
                if ($course[$level]['course_status'] == 'APROBADO'):
                    $tmpExcelApprovedFlag = true;   
                endif;
            endforeach;
    endif;
endforeach;
@endphp
@if ($hasExcelCourseToNotify == true && tmpExcelApprovedFlag == true)
Es decir, que *aunque hayas aprobado ese nivel, no recibirás certificación alguna porque la condición para certificar Excel Empresarial, es que hayas aprobado todos los niveles que lo comprenden.*
@endif

{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@php
$tmpDisapprovedCourses = count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}));
@endphp
@if($showInProgressOtherCourses == true && $tmpDisapprovedCourses >= 2)
Por lo que, al tener ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:

    {{-- Fila 56: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO CURSANDO --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
    Como aún no te certificas en SAP, al haber reprobado solo un curso, aún mantienes el acceso a:
    {{-- Fila 57: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y si es curso OBSEQUIO CURSANDO --}}
    @elseif(($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false))
    Al no haberte certificado en SAP y tener ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:
    @else
    Al no haberte certificado en SAP y tener este curso reprobado, como te comenté anteriormente pierdes el acceso a este curso, a pesar de haberlo iniciado:
    @endif
    @foreach ($coursesToNotify as $course)
    {{$course['name']}}
    @endforeach
@endif


{{-- VARIANTE Filas 61 a 85: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showDissaprovedOtherCourses == true )
Por lo que, al haber reprobado SAP y también:
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'REPROBADO')
        {{$course['name']}}
        @endif
    @endforeach

    {{-- Fila 63: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO COMPLETA REPROBADO o SIN INTENTOS GRATIS, que TERMINE en OTRA FECHA o ya haya terminado. Se tomará EXCEL como reprobado, si tiene al menos un nivel reprobado.  --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
    Como aún no te certificas en SAP, al haber reprobado solo un curso:
        @foreach ($coursesToNotify as $course)
    {{$course['name']}}
        @endforeach

        @foreach ($otherFreeCourses as $course)
            @if ($course['course_status_original'] == 'POR HABILITAR')
            Puedes habilitar:
            {{$course['name']}}
            Solo que, para iniciar este curso, esperaremos a que apruebes SAP para poder habilitarlo.
            Solo que, para iniciar estos cursos, esperaremos a que apruebes SAP para poder habilitarlos.

            @elseif ($course['course_status_original'] == 'CURSANDO')
            Aún mantienes el acceso a:
            {{$course['name']}}
            @elseif ($course['course_status_original'] == 'APROBADO')
            Cuando recibas tu certificado en SAP, podrás recibir también el certificado con aval internacional de:
            {{$course['name']}}
            @endif
        @endforeach
    @endif
@endif


{{-- Fila 90: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO NO CULMINÓ --}}
@if($showUnfinishedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    @if(count($coursesToNotify) == 1)
    Como aún no te certificas en SAP, reprobaste un curso y no culminaste:
    @elseif(count($coursesToNotify) > 1)
    Como aún no te certificas en SAP, reprobaste dos cursos y no culminaste:
    @endif

    @if(($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Por lo que, como también reprobaste SAP y no culminaste con:
    @endif
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'NO CULMINÓ')
        {{$course['name']}}
        @endif
    @endforeach

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        No puedes habilitar:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'CURSANDO')
        Pierdes el acceso a:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'APROBADO')
        No tendrás el certificado internacional:
        {{$course['name']}}
        @endif
    @endforeach

    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO"))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @elseif(($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
    @endif

@endif


{{-- Filas 567 a 591: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showDroppedOtherCourses == true )

    {{-- Fila 117: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ  --}}
    @if($showDroppedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Como aún no te certificas en SAP, reprobaste dos cursos y abandonaste:
    @elseif($showDroppedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Por lo que, como también reprobaste SAP y no culminaste con:

    @endif

    @foreach ($coursesToNotify as $course)
        {{$course['name']}}
    @endforeach

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        No puedes habilitar:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'CURSANDO')
        Pierdes el acceso a:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'APROBADO')
        No tendrás el certificado internacional:
        {{$course['name']}}
        @endif
    @endforeach

    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO"))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @elseif(($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
    @endif
@endif

{{-- VARIANTE Fila 129: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showDroppedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))

    @if($showDroppedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Como aún no te certificas en SAP y reprobaste dos cursos:
    @elseif($showDroppedOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Por lo que, como también reprobaste SAP:

    @endif

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status_original'] == 'POR HABILITAR')
        No podrás habilitar:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'CURSANDO')
        Pierdes el acceso a:
        {{$course['name']}}
        @elseif ($course['course_status_original'] == 'APROBADO')
        Pierdes el certificado internacional:
        {{$course['name']}}
        @endif
    @endforeach

    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO"))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
    @elseif(($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
    Ya que tendrías ({{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status_original'] === 'REPROBADO';}))}}) cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
    @endif
@endif


🚩 🚩 *¡AÚN ES POSIBLE LOGRAR QUE TE CERTIFIQUES!* No pierdas lo que ya has logrado.
⏳ *¡Actúa ya!* Paga HOY con un precio especial el ponderado, ¡no pierdas esta oportunidad! 
*Eso sí, el pago debe ser dentro de mi horario laboral.*

@if(($studentData["AULA SAP"] == "NO APLICA" ))
Si, por el contrario, deseas realizar tu pago mañana u otro día, tendrás que rematricularte con el precio regular de este curso.
@else
Si, por el contrario, deseas realizar tu pago mañana u otro día, tendrás que matricularte con el precio regular de este curso.* Ya que te recuerdo, que esto fue un obsequio por haberte matriculado en SAP.
@endif

⚠️ *Importante: Pagos fuera de mi horario laboral no serán reconocidos. No habrá reembolsos, tendrás que completar el valor para rematrícula.*

@if (count($coursesToNotify) == 1)
*Ha sido una lástima no contar con tu participación en esta certificación.*

@else
*Ha sido una lástima no contar con tu participación en estas certificaciones.*

@endif
