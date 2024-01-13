@php
// cache interna
$otherFreeCoursesInProgressNames = [];
$otherFreeCoursesDissaprovedNames = [];
$otherFreeCoursesDroppedNames = [];
$otherFreeCoursesUnfinishedNames = [];
$otherFreeCoursesApprovedNames = [];
$otherFreeCoursesToEnableNames = [];
$otherFreeCoursesCompletedNames = [];

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
        case 'COMPLETA':
            $otherFreeCoursesCompletedNames[] = $course['name'];
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
¡Hola!
{{$studentData['NOMBRE']}}

@if (count($coursesToNotify) == 1)
Quería recordarte que ya se acerca el término del tiempo brindado para llevar tu curso:
@else
Quería recordarte que ya se acerca el término del tiempo brindado para llevar tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

Es decir que tendrás acceso al contenido del curso SAP y al software, *hasta el día:*
{{$endCourseDate->format('d/m/Y')}}

@php 
$tmpShowSectionFlag = false;
if(count($otherSapCoursesToEnableNames) > 0 || count($otherFreeCoursesToEnableNames) > 0):
    $tmpShowSectionFlag = true;
endif;
$tmpCourseNames = array_merge($otherSapCoursesToEnableNames, $otherFreeCoursesToEnableNames);
@endphp
@if($tmpShowSectionFlag)
*Sé que te certificaste* 🎓📜 así que quería consultarte si ya deseas iniciar con: 
    @foreach ($tmpCourseNames as $courseName)
        {{$courseName}}
    @endforeach
@endif

@php 
// Filas 16 a 30: Fila 16: si tiene un solo curso de obsequio con estado: CURSANDO, COMPLETA, es decir, que no sean POR HABILITAR.
// Fila 17: si tiene al menos un curso de obsequio con estados: CURSANDO, COMPLETA, es decir, que no sean POR HABILITAR.

$tmpShowSectionFlag = false;
if(
    (
        count($otherFreeCoursesInProgressNames) > 0 || 
        count($otherFreeCoursesCompletedNames) > 0
    )
    && 
    (
        count($otherFreeCoursesToEnableNames) == 0
    )
    ):
    $tmpShowSectionFlag = true;
endif;
$tmpCourseNames = array_merge($otherFreeCoursesInProgressNames, $otherFreeCoursesCompletedNames);

$tmpCoursesDissaprovedNames = array_merge($otherSapCoursesDissaprovedNames, $otherFreeCoursesDissaprovedNames);
$tmpCoursesDroppedNames = array_merge($otherSapCoursesDroppedNames, $otherFreeCoursesDroppedNames);
$tmpCoursesUnfinishedNames = array_merge($otherSapCoursesUnfinishedNames, $otherFreeCoursesUnfinishedNames);
$tmpCoursesApprovedNames = array_merge($otherSapCoursesCertifiedNames, $otherFreeCoursesApprovedNames);
$tmpCoursesToEnableNames = array_merge($otherSapCoursesToEnableNames, $otherFreeCoursesToEnableNames);

@endphp
@if($tmpShowSectionFlag)
    @if(count($tmpCourseNames) == 1)
    *Sé que te certificaste* 🎓📜 así que  te recuerdo el estado de tu curso:
   @else
   *Sé que te certificaste* 🎓📜 así que  te recuerdo el estado de los demás cursos:
    @endif

    @if(count($otherFreeCoursesInProgressNames) > 0)
Aún estás *cursando:*
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($tmpCoursesDissaprovedNames) > 0)
Completaste pero *REPROBASTE:*
{{implode("\n", $tmpCoursesDissaprovedNames)}}
    @endif
    @if(count($tmpCoursesUnfinishedNames) > 0)
*No culminaste:*
{{implode("\n", $tmpCoursesUnfinishedNames)}}
    @endif
    @if(count($tmpCoursesDroppedNames) > 0)
*Abandonaste:*
{{implode("\n", $tmpCoursesDroppedNames)}}
    @endif
    @if(count($tmpCoursesToEnableNames) > 0)
Aún tienes *por habilitar:*
{{implode("\n", $tmpCoursesToEnableNames)}}
Por favor me indicas si *deseas iniciar de una vez,* para enviarte las *fechas disponibles.*
    @endif
    @if(count($tmpCoursesApprovedNames) > 0)
*Aprobaste:*
{{implode("\n", $tmpCoursesApprovedNames)}}
   @endif
@endif

@if($show6CoursesOffer == true)
También quería saber si te interesaría llevar otro curso de SAP con nosotros,🤩 *con un precio increíble por ser ex alumno.* 🤯
Coméntame y te envío los cursos que tenemos disponibles en este momento, así como las *certificaciones máster* que los acompañan.
@endif

{{-- Fila 35: solo si en las columnas de los nombres de los cursos de obsequio, se encuentra el estado NO APLICA --}}
@if($showOtherFreeCourseOffer == true)
También tenemos disponible *el paquete Office: Excel Empresarial 3 niveles, Power BI y MS Project,* que también podría interesarte.
@elseif($showSecondChanceOtherFreeCourseOffer == true)
También tenemos disponible *el paquete Office: Excel Empresarial 3 niveles, Power BI y MS Project,* que también podría interesarte, ya que no lograste certificarte.
@endif


Quedo atenta a tus posibles consultas y a que me indiques si deseas que te envíe de una vez las *fechas de inicio de los cursos de obsequio.*

