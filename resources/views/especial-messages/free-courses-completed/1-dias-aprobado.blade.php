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

"PLANTILLAS CURSO OBSEQUIOS COMPLETOS Y APROBADO"
FALTANDO 1 DIA PARA LA FECHA FIN DEL CURSO

CURSO: FREE
ESTADO CURSO: COMPLETO
ESTADO DE EXAMEN: APROBADO
FECHA DE FIN DE CURSO: 1 dia hacia delante

--}}
¡Hola!
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información del curso:
@else
Te saludo del área académica de *Global Tecnologías Academy* 🤓, para enviarte la última información de los cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy a las 23:59, tu aula virtual será eliminada.*
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada.*
@endif


@if (count($coursesToNotify) == 1)
Sé que te certificaste en todos los cursos, pero es mi deber informarte que ya ha culminado el plazo de tu aula virtual.
@else
Sé que te certificaste, pero es mi deber informarte que ya ha culminado el plazo de tu aula virtual.
@endif


{{-- Fila 17: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y solo para los cursos PBI o MSP con estado EXAMEN APROBADO --}}
@if($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que el certificado por este curso aprobado, lo recibirás cuando te certifiques en SAP.
{{-- Filas 18: Si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO y cuando sea PBI y/o MSP y/o EXCEL con estado EXAMEN APROBADO  --}}
@elseif($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que los certificados por estos cursos aprobados, los recibirás cuando te certifiques en SAP.
@elseif($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == true && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
⚠️ *Importante:* Como aún tienes tu curso SAP activo, te recuerdo que los certificados por este curso aprobado, los recibirás cuando te certifiques en SAP.
@endif
