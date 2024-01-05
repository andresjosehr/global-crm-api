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

@endphp
{{--

"PLANTILLAS CURSO SAP CON INTENTOS PENDIENTES"
FALTANDO 1 DIA PARA LA FECHA FIN DEL CURSO

CURSO: SAP
ESTADO DE EXAMEN: CON INTENTOS PENDIENTES
FECHA DE FIN DE CURSO: 1 dia hacia delante

--}}
*¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:*
{{$studentData['NOMBRE']}}


@if (count($coursesToNotify) == 1)
Está por vencer tu curso:
@else
Están por vencer tus cursos:
@endif
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@endif


{{-- Variante para INTENTOS PENDIENTES --}}
Por lo que, *a partir del envío de este mensaje, el tiempo mínimo para extender, es de 3 meses.* Y tienes como máximo el plazo de una semana a partir de hoy, para realizar el pago, solo que, el aula que recibirás estará completamente vacía, porque no guardamos tu avance posterior a las 23:59.
*Ojo, si esperas al último minuto de mi jornada laboral de hoy, no podré realizar los trámites necesarios y tampoco realizamos devoluciones.*

{{-- Cursos SAP anteriores --}}
@foreach ($otherSapCourses as $course)
    @if ($course["course_status_original"] == "CERTIFICADO")
Recuerda que antes aprobaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "REPROBADO")
Recuerda que antes reprobaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "ABANDONADO")
Recuerda que antes abandonaste:
{{$course['name']}}
    @elseif ($course["course_status_original"] == "NO CULMINÓ")
Recuerda que antes no culminaste:
{{$course['name']}}
    @endif
@endforeach


{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
{{-- Filas 360 a 372: si se utilizan las filas 355, 356 y/o 357. También si se utiliza la fila 354 CON alguna de las filas desde 355 a 357.  --}}
@php
$tmpFlag = false;
foreach ($otherSapCourses as $course):
    if ($course["course_status_original"] == "REPROBADO" || $course["course_status_original"] == "ABANDONADO" || $course["course_status_original"] == "NO CULMINÓ"):
        $tmpFlag = true;
    endif;
endforeach;
@endphp
@if ($tmpFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus cursos de obsequio te comento:*
    @if(count($otherFreeCoursesInProgressNames) > 0)
Aún estás *cursando:*
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesDissaprovedNames) > 0)
Completaste pero *REPROBASTE:*
{{implode("\n", $otherFreeCoursesDissaprovedNames)}}
    @endif
    @if(count($otherFreeCoursesUnfinishedNames) > 0)
*No culminaste:*
{{implode("\n", $otherFreeCoursesUnfinishedNames)}}
    @endif
    @if(count($otherFreeCoursesDroppedNames) > 0)
*Abandonaste:*
{{implode("\n", $otherFreeCoursesDroppedNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
Aún tienes *por habilitar:*
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
*Aprobaste:*
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif
@endif


{{-- Filas 374 a 380: si en la columna SAP es UN solo curso --}}
@php
$tmpShowSectionFlag = false;
if(count($coursesToNotify) == true):
    foreach ($coursesToNotify as $course):
        if ($course['course_status'] == 'CURSANDO' || $course['course_status'] == 'POR HABILITAR' || $course['course_status'] == 'APROBADO'):
            $tmpShowSectionFlag = true;
        endif;
    endforeach;
endif;
@endphp
{{-- mostrar cursos obsequios de advertencia --}}
@if ($tmpShowSectionFlag)
Por lo que, *al no haberte certificado en SAP, que es el curso principal:*
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haberlo habilitado, pierdes el acceso a:
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Pierdes la posibilidad de iniciar:
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif    
    @if(count($otherFreeCoursesApprovedNames) > 0)
    Y a pesar de haber aprobado, pierdes el certificado internacional de:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif    

@endif

{{-- Filas Filas 381 a 387: si se utiliza la fila 355 (REPROBADO). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 355 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;
$tmpSapDisapprovedCourseNames = [];
$tmpSapCourseToEnableFlag = false; // asume q no hay cursos SAP por habilitar
$tmpCourseToNotifyNames = array_column($coursesToNotify, 'name');
foreach ($otherSapCourses as $course):
    if ( $course['course_status'] == 'REPROBADO'):
        $tmpSapDisapprovedCourseNames[] = $course["course_name"];
    elseif ( $course['course_status'] == 'POR HABILITAR'):
        $tmpSapCourseToEnableFlag = true;
    endif;
endforeach;

$tmpHasFreeCoursesToShow = false;
foreach ($otherFreeCourses as $course):
    if ($course['course_status'] == 'CURSANDO' || $course['course_status'] == 'POR HABILITAR' || $course['course_status'] == 'APROBADO'):
        $tmpHasFreeCoursesToShow = true;
    endif;
endforeach;

if(count($tmpSapDisapprovedCourseNames) > 0 && $tmpSapCourseToEnableFlag == false && $tmpHasFreeCoursesToShow == true):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haber reprobado ({{implode(', ', $tmpSapDisapprovedCourseNames)}}) y no haberte certificado en ({{implode(', ', $tmpSapDisapprovedCourseNames)}}):
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haberlo habilitado, pierdes el acceso a:
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Pierdes la posibilidad de iniciar:
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif    
    @if(count($otherFreeCoursesApprovedNames) > 0)
    Y a pesar de haber aprobado, pierdes el certificado internacional de:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif    
@endif


{{-- Filas 388 a 394: si se utiliza la fila 356 (ABANDONASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 356 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;
$tmpSapDroppedCourseNames = [];
$tmpSapCourseToEnableFlag = false; // asume q no hay cursos SAP por habilitar
$tmpCourseToNotifyNames = array_column($coursesToNotify, 'name');
foreach ($otherSapCourses as $course):
    if ( $course['course_status'] == 'ABANDONADO'):
        $tmpSapDroppedCourseNames[] = $course["course_name"];
    elseif ( $course['course_status'] == 'POR HABILITAR'):
        $tmpSapCourseToEnableFlag = true;
    endif;
endforeach;

$tmpHasFreeCoursesToShow = false;
foreach ($otherFreeCourses as $course):
    if ($course['course_status'] == 'CURSANDO' || $course['course_status'] == 'POR HABILITAR' || $course['course_status'] == 'APROBADO'):
        $tmpHasFreeCoursesToShow = true;
    endif;
endforeach;

if(count($tmpSapDroppedCourseNames) > 0 && $tmpSapCourseToEnableFlag == false && $tmpHasFreeCoursesToShow == true):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haber abandonado ({{implode(', ', $tmpSapDroppedCourseNames)}}) y no haberte certificado en ({{implode(', ', $tmpSapDisapprovedCourseNames)}}:
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haberlo habilitado, pierdes el acceso a:
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Pierdes la posibilidad de iniciar:
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif    
    @if(count($otherFreeCoursesApprovedNames) > 0)
    Y a pesar de haber aprobado, pierdes el certificado internacional de:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif    

@endif



{{-- Filas 395 a 401: si se utiliza la fila 357 (NO CULMINASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 357 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar --}}
@php
    $tmpShowSectionFlag = false;
    $tmpSapUnfinishedCourseNames = [];
    $tmpSapCourseToEnableFlag = false; // asume q no hay cursos SAP por habilitar
    $tmpCourseToNotifyNames = array_column($coursesToNotify, 'name');
    foreach ($otherSapCourses as $course):
        if ( $course['course_status'] == 'NO CULMINÓ'):
            $tmpSapUnfinishedCourseNames[] = $course["course_name"];
        elseif ( $course['course_status'] == 'POR HABILITAR'):
            $tmpSapCourseToEnableFlag = true;
        endif;
    endforeach;

    $tmpHasFreeCoursesToShow = false;
    foreach ($otherFreeCourses as $course):
        if ($course['course_status'] == 'CURSANDO' || $course['course_status'] == 'POR HABILITAR' || $course['course_status'] == 'APROBADO'):
            $tmpHasFreeCoursesToShow = true;
        endif;
    endforeach;

    if(count($tmpSapUnfinishedCourseNames) > 0 && $tmpSapCourseToEnableFlag == false && $tmpHasFreeCoursesToShow == true):
        $tmpShowSectionFlag = true;
    endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al no haber culminado ({{implode(', ', $tmpSapUnfinishedCourseNames)}}) y no haberte certificado en ({{implode(', ', $tmpSapDisapprovedCourseNames)}}:
    @if(count($otherFreeCoursesInProgressNames) > 0)
    A pesar de haberlo habilitado, pierdes el acceso a:
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Pierdes la posibilidad de iniciar:
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif    
    @if(count($otherFreeCoursesApprovedNames) > 0)
    Y a pesar de haber aprobado, pierdes el certificado internacional de:
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif        
@endif

{{--  Filas 402 a 410: si se utiliza la fila 354 (APROBASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 354 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;
$tmpSapApprovedCourseNames = [];
$tmpSapCourseToEnableFlag = false; // asume q no hay cursos SAP por habilitar
$tmpCourseToNotifyNames = array_column($coursesToNotify, 'name');
foreach ($otherSapCourses as $course):
    if ( $course['course_status'] == 'APROBADO'):
        $tmpSapApprovedCourseNames[] = $course["course_name"];
    elseif ( $course['course_status'] == 'POR HABILITAR'):
        $tmpSapCourseToEnableFlag = true;
    endif;
endforeach;

$tmpHasFreeCoursesToShow = false;
$tmpHasFreeCoursesInProgressToShow = false;
foreach ($otherFreeCourses as $course):
    if ($course['course_status'] == 'CURSANDO' || $course['course_status'] == 'POR HABILITAR' ):
        $tmpHasFreeCoursesToShow = true;
    endif;
    if ($course['course_status'] == 'CURSANDO'):
        $tmpHasFreeCoursesInProgressToShow = true;
    endif;
endforeach;

if(count($tmpSapApprovedCourseNames) > 0 && $tmpSapCourseToEnableFlag == false && $tmpHasFreeCoursesToShow == true):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haberte certificado anteriormente en ({{implode(', ', $tmpSapApprovedCourseNames)}}), aunque no te certificaste en ({{implode(', ', $tmpSapDisapprovedCourseNames)}}):

    @if(count($otherFreeCoursesInProgressNames) > 0)
    Puedes seguir *cursando:*
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Aún puedes *habilitar:*
{{implode("\n", $otherFreeCoursesToEnableNames)}}
        @if($tmpHasFreeCoursesInProgressToShow == true)
                Pero ten en cuenta que si no te certificas en este curso, pierdes automáticamente la posibilidad de habilitar:
                {{$course['name']}}
        @endif    
    @endif    
@endif


{{-- ATENCION Excel Filas 412 a 416 --}}
@if ($showWarningSapCourseCertificationFlag == true && $toEnableFreeCoursesCount == 1)
A continuación te envío las fechas de inicio para habilitarlo:
@elseif ($showWarningSapCourseCertificationFlag == true && $toEnableFreeCoursesCount > 1)
A continuación te envío las fechas de inicio para habilitarlos:
@endif
@if ($showWarningSapCourseCertificationFlag == true && $toEnableFreeCoursesCount >= 1)
    @foreach ($toEnableFreeCoursesDates as $date)
{{$date->format('d/m/Y')}}
    @endforeach
@endif


Tienes como máximo una semana para escoger al menos la última fecha de inicio, posterior a ella, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.

{{-- Filas 418 a 430: si se usan las filas 355 a 357 (reprobaste, abandonaste o no culminaste). Fila 418: si tiene UN curso SAP como PENDIENTE en la columna de ESTADO. Colocar en los "()" el nombre del curso o cursos SAP que esten como reprobados, abandonados o no certificados, incluyendo la fila 341 --}}
@php
    $tmpShowSectionFlag = false;
    $tmpSapOtherNamesNames = []; // reprobados, abandonados o no certificados
    $tmpSapPendingCourseNames = []; // por habilitar
    $tmpApprovedSectionFlag = false;
    $tmpApprovedSectionNames = []; // sap no culminaste y sap a notificar
    $tmpCourseToNotifyNames = array_column($coursesToNotify, 'name');

    foreach ($otherSapCourses as $course):
        if ($course['course_status'] == 'REPROBADO' || $course['course_status'] == 'ABANDONADO' ||  $course['course_status'] == 'NO CULMINÓ' ):
            $tmpShowSectionFlag = true;
        endif;
        if ($course['course_status'] == 'REPROBADO' || $course['course_status'] == 'ABANDONADO' ||  $course['course_status'] != 'CERTIFICADO' ):
            $tmpShowSectionFlag = true;
            $tmpSapOtherNamesNames[] = $course["course_name"];
        endif;        
        if ($course['course_status'] == 'POR HABILITAR'):
            $tmpSapPendingCourseNames[] = $course["course_name"];
        endif;
        if ($course['course_status'] == 'APROBADO'):
            $tmpApprovedSectionFlag = true;
        endif;        
        if ($course['course_status'] == 'REPROBADO' || $course['course_status'] == 'ABANDONADO' ||  $course['course_status'] == 'NO CULMINÓ' ):
            $tmpApprovedSectionNames[] = $course["course_name"];
        endif;
    endforeach;
@endphp
@if ($tmpShowSectionFlag)
    @if(count($tmpSapPendingCourseNames) == 1)
    Por lo que, al no haberte certificado en ({{implode(', ', $tmpSapOtherNamesNames)}}), tienes como máximo 15 días para iniciar con el siguiente curso SAP ofrecido:
    @else
    Por lo que, al no haberte certificado en ({{implode(', ', $tmpSapOtherNamesNames)}}), tienes como máximo 15 días para iniciar con los siguientes cursos SAP ofrecidos:
    @endif

    @if ($tmpApprovedSectionFlag == true)
        @if(count($tmpSapPendingCourseNames) == 1)
        Por lo que, al haberte certificado en ({{implode(', ', $tmpApprovedSectionNames)}}), aunque no te hayas certificado en ({{implode(', ', $tmpCourseToNotifyNames)}}), puedes iniciar como máximo en 15 días con el siguiente curso SAP ofrecido:
        @else
        Por lo que, al haberte certificado en ({{implode(', ', $tmpApprovedSectionNames)}}), aunque no te hayas certificado en ({{implode(', ', $tmpCourseToNotifyNames)}}), puedes iniciar como máximo en 15 días con los siguientes cursos SAP ofrecidos:
        @endif
    @endif

    @foreach ($otherSapCourses as $course)
        @if ($course['course_status'] == 'POR HABILITAR')
{{$course['name']}}
        @endif
    @endforeach

    @if(count($tmpSapPendingCourseNames) == 1)
    Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, lo estarás perdiendo.
    @else
    Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.
    @endif
    
    @if($tmpSapPendingCourseNames == 1)
    A continuación te envío las fechas de inicio para habilitarlo:
    @elseif($tmpSapPendingCourseNames > 1)
    A continuación te envío las fechas de inicio para habilitarlos:
    @endif

    @foreach ($toEnableSapCoursesDates as $date)
{{$date->format('d/m/Y')}}
    @endforeach    
@endif


*Lamentamos no contar con tu participación en la certificación de Key User SAP.*

*Te recuerdo por última vez que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* No habrán devoluciones de ningún tipo, si el pago es enviado fuera de mi horario, así sea por un minuto. 

Saludos cordiales.