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
@endphp
{{--

"PLANTILLAS CURSO SAP CERTIFICADOS  CON CURSOS PENDIENTES
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO CURSO: CERTIFICADO
ESTADO DE EXAMEN: CERTIFICADO
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
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
🚨 *Hoy las 23:59, tu aula virtual de SAP será eliminada, perdiendo también el acceso a tu aplicativo de SAP.*
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual de SAP será eliminada, perdiendo también el acceso a tu aplicativo de SAP.*
@endif

*Sé que te certificaste* 🎓📜 por lo que te comento: 

@if(count($pendingOtherFreeCourses) > 1)
Tienes como *máximo 7 días,* para escoger *una de estas fechas* de inicio:

    @if(count($pendingOtherFreeCourses) == 1)
Para habilitar tu curso:
    @else
Para habilitar tus cursos:
    @endif
    @foreach ($pendingOtherFreeCourses as $course)
    {{$course['name']}}
    @endforeach
@endif

{{-- Filas 56 a 59: Fila 56: cuando sea un curso SAP con estado en la columna de estado PENDIENTE --}}
@if(count($pendingOtherSapCourses) > 0 )
Tienes como *máximo 15 días,* para escoger *una de estas fechas* de inicio:
    @foreach ($pendingOtherSapCourses as $course)
    {{$course['name']}}
    @endforeach
@endif

@if(count($pendingOtherFreeCourses) > 0 || count($pendingOtherSapCourses) > 0)
    @if(count($pendingOtherFreeCourses) == 1 && count($pendingOtherSapCourses) == 1)
    De lo contrario, si no recibimos confirmación de tu parte, lo estarás perdiendo y no podrás recuperarlo luego.
    @else
    De lo contrario, si no recibimos confirmación de tu parte, los estarás perdiendo y no podrás recuperarlos luego.
    @endif

@endif

De lo contrario, si no recibimos confirmación de tu parte, los estarás perdiendo y no podrás recuperarlos luego.
