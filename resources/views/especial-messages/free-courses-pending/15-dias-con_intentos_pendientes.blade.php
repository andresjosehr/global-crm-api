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
⚠️ ¡Atención urgente! ⏳
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante si es PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
🚨 Recuerda que para poder certificarte, debes *aprobar el examen correspondiente,* de lo contrario, no obtendrás ningún certificado, porque *no brindamos certificado por participación.* Y actualmente este es el avance que llevas:
{{-- Variante si es PBI o MSP con Excel--}}
{{-- Variante si es solo Excel--}}
@else
🚨 Recuerda que para poder certificarte, debes *aprobar el examen correspondiente a cada nivel,* de lo contrario, no obtendrás ningún certificado, porque *no brindamos certificado por participación, ni por niveles independientes.* Y actualmente este es el avance que llevas:
@endif
@foreach ($coursesToNotify as $course)
    @if ($course['isExcelCourse'] == false)
       {{$course['name']}}, tiene {{$course['lessons_completed']}} lecciones completas, y en total son {{$course['lessons_count']}}.
        @else
            @foreach($course['LEVELS'] as $level)
                {{$course['name']}} - {{$course[$level]['name']}}, tiene {{$course[$level]['lessons_completed']}} lecciones completas, y en total son {{$course[$level]['lessons_count']}}.
            @endforeach
    @endif
@endforeach

📌 Y la fecha fin es el día:
{{$endCourseDate->format('d/m/Y')}}

@if ($hasIncompleteLessons == true)
🙌 Si sientes que no podrás certificarte antes de la fecha de vencimiento, *recuerda que tienes la solución: EXTENDER LA DURACIÓN DE TU AULA VIRTUAL y mantener los beneficios que tienes actualmente.*
@endif

🚩🚩 Sin embargo, *esta oportunidad solo está disponible si realizas el pago en este momento,* ya que a partir de la siguiente semana, las condiciones de extensión serán otras.
No dejes que el tiempo se agote⏳. *Actúa ahora y asegúrate de mantener tu camino hacia la certificación.*

📌 *RECUERDA* que si en dado caso apruebas algún examen de certificación *antes de la fecha mencionada,* debes indicarme para comentarte los pasos a seguir. Si en dado caso aprobaste y aún no me has indicado, lo podrías perder el día de la fecha de fin antes mencionada.


{{-- Fila 30: solo si AULA SAP tiene el estado CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
@if($studentData["AULA SAP"]  == "CURSANDO" && $studentData["CERTIFICADO"] != "EMITIDO")
Aprovecho para recordarte que para obtener el certificado al aprobar, tendrás que certificarte primero en SAP. 
@endif


{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)
👀 *OJO también estás cursando:*
{{implode(', ', $otherFreeCoursesInProgressNames)}}
    {{-- Fila 38: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP.
    {{-- Fila 39: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y si es curso OBSEQUIO CURSANDO --}}
    @elseif(($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro este curso, si no te certificas en:
    @endif
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach
@endif


{{-- VARIANTE Filas 43 a 55: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showDissaprovedOtherCourses == true )
👀 *OJO completaste, pero reprobaste:*
{{implode(', ', $otherFreeCoursesDissaprovedNames)}}
        @endif
@if($showDissaprovedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($otherFreeCoursesToEnableNames) > 0)
A pesar de quedar pendiente, no podrás habilitar:
    {{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesInProgressNames) > 0)
A pesar de haber iniciado, perderías el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
        @endif

Ya que tendrías {{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status'] === 'REPROBADO';}))}} cursos reprobados/abandonados, así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif


{{-- VARIANTE Filas 70 a 82: Filas 70 a 94: si tiene curso obsequio con estado NO CULMINÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showUnfinishedOtherCourses == true )
👀 *OJO: recuerda que no culminaste:*
{{implode(', ', $otherFreeCoursesUnfinishedNames)}}
@endif
{{-- Fila 72: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO NO CULMINÓ --}}
@if($showUnfinishedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($otherFreeCoursesToEnableNames) > 0)
    A pesar de quedar pendiente, no podrás habilitar:
    {{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haber iniciado, perderías el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
    A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif     

Ya que tendrías {{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status'] === 'REPROBADO';}))}} cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif

{{-- VARIANTE Filas Filas 97 a 121: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@if($showDroppedOtherCourses == true )
👀 *OJO: recuerda que abandonaste:*
{{implode(', ', $otherFreeCoursesDroppedNames)}}
@endif
{{-- Fila 99: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ --}}
@if($showDroppedOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($otherFreeCoursesToEnableNames) > 0)
    A pesar de quedar pendiente, no podrás habilitar:
    {{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haber iniciado, perderías el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
    A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif

Ya que tendrías {{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status'] === 'REPROBADO';}))}} cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif

{{-- VARIANTE Filas 124 a 144: si tiene curso obsequio con estado POR HABILITAR, con las condiciones específicas de cada fila: --}}
@if($showToEnableOtherCourses == true )
👀 *OJO tienes por habilitar:*
{{implode(', ', $otherFreeCoursesToEnableNames)}}
@endif
{{-- Fila 99: Fila 126: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y es curso OBSEQUIO ABANDONÓ --}}
@if($showToEnableOtherCourses == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP. Por lo que si no te certificas en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haber iniciado, perderías el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
    A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif    
  

Ya que tendrías {{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status'] === 'REPROBADO';}))}} cursos reprobados/abandonados, , así que *solo quedaría pendiente tu curso SAP, porque no tendrías más cursos por habilitar.*
@endif

{{-- VARIANTE Fila 136: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
@if($showToEnableOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') !== false))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que si no te certificas en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haber iniciado, perderías el acceso a:
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
    A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif   

Ya que tendrías {{count(array_filter($otherFreeCourses, function ($course) {return $course['course_status'] === 'REPROBADO';}))}} cursos reprobados/abandonados, *siendo tu último procedimiento con nosotros, porque no tendrías más cursos por habilitar.*
@endif

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.
