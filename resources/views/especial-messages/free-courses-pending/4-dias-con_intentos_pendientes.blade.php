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
@if (count($coursesToNotify) == 1)
¡Urgente, tu curso está en peligro! ⚠️
@else
¡Urgente, tus cursos están en peligro! ⚠️
@endif

Tenemos importantes noticias sobre las condiciones de extensión de:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Fila 30: solo si AULA SAP tiene el estado CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
@if($studentData["AULA SAP"]  == "CURSANDO" && $studentData["CERTIFICADO"] != "EMITIDO")
🚨 *Aún puedes extender tu curso por 2 meses,* para poder certificarte cuando apruebes el curso de SAP.
@elseif($studentData["CERTIFICADO"] == "EMITIDO")
🚨 *Aún puedes extender tu curso por 2 meses,* para poder certificarte.
@endif

Sin embargo, en menos de 72h la extensión mínima será de 3 meses y perderás el avance que llevas en tu aula virtual:
{{-- Variante si es PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @foreach($coursesToNotify)
    {{$course['lessons_completed']}} lecciones completas, y en total son {{$course['lessons_count']}}; y aún cuentas con {{$course["certifaction_test_original"]}}.
    @endforeach
{{-- Variante si es PBI o MSP con Excel--}}
{{-- Variante si es solo Excel--}}
@else
    @foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == false)
        {{$course['name']}}, tiene {{$course['lessons_completed']}} lecciones completas, y en total son {{$course['lessons_count']}}; y aún cuentas con {{$course["certifaction_test_original"]}}.
        @else
                @foreach($course['LEVELS'] as $level)
                {{$course['name']}} - {{$course[$level]['name']}}, tiene {{$course[$level]['lessons_completed']}} lecciones completas, y en total son {{$course[$level]['lessons_count']}}; y aún cuentas con {{$course[$level]["certifaction_test_original"]}}.
                @endforeach
        @endif
    @endforeach
@endif

*Te recomendamos encarecidamente que consideres la opción de extender el tiempo de tu aula virtual o aprobar tu examen de certificación, antes del:*
{{$endCourseDate->format('d/m/Y')}}

⚠️ Ya que, si decides esperar hasta el último día para realizar el pago de la extensión, debes hacerlo dentro de mi horario laboral, ya que el sistema elimina automáticamente tus accesos a las 23:59 horas.
Esto significa que no se conservará ningún respaldo de tu progreso y lamentablemente no podremos aceptar capturas de pantalla como prueba para apoyarte con algún certificado.

🚩 *Si en dado caso decides realizar el pago fuera de mi horario laboral, el mismo no será reconocido y tu aula será eliminada porque no habrá quien reporte tu pago:*
*Teniendo así que ajustarte a las condiciones de extensión mencionadas anteriormente completando el valor faltante, ya que no realizaremos ninguna devolución por el pago realizado.*

@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
    @if (count($coursesToNotify) == 1)
    Una vez más te recuerdo que no brindamos certificados por haber completado el curso, ni por participación.
    @else
    Una vez más te recuerdo que no brindamos certificados por haber completado los cursos, ni por participación.
    @endif
@elseif ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true)
Una vez más te recuerdo que no brindamos certificados por haber completado el curso, ni por participación, ni por niveles independientes.
@elseif ($hasSpecializedCoursesToNotify == false && $hasExcelCourseToNotify == true)
TUna vez más te recuerdo que no brindamos certificados por haber completado el curso, ni por participación, ni por niveles independientes de Excel.
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
@if($showToEnableOtherCourses == true && ($studentData["EXAMEN"] == "REPROBADO" || stripos($studentData['EXAMEN'], 'Sin intentos Gratis') === false))
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


*Aprovecho para comentarte que toda solicitud y pago de extensión, debe ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).*
