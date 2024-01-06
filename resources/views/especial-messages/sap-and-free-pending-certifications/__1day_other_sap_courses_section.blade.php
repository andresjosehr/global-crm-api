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

$sapCoursesNames = array_column($sapCourses, 'name');

@endphp
{{--

    SUBPLANTILLA PARA INFORMAR LOS AVANCES DE CURSO SAP Y CURSOS DE OBSEQUIO
    "Recuerda que antes aprobaste SAP 1..."

--}}
{{-- Cursos SAP anteriores --}}
@if(count($otherSapCourses) > 0)
    @if(count($otherSapCoursesCertifiedNames) > 0)
Recuerda que antes aprobaste:
{{implode("\n", $otherSapCoursesCertifiedNames)}}
    @endif
    @if(count($otherSapCoursesDissaprovedNames) > 0)
    Recuerda que antes reprobaste:
{{implode("\n", $otherSapCoursesDissaprovedNames)}}
    @endif
    @if(count($otherSapCoursesDroppedNames) > 0)
    Recuerda que antes abandonaste:
{{implode("\n", $otherSapCoursesDroppedNames)}}
    @endif    
    @if(count($otherSapCoursesUnfinishedNames) > 0)
    Recuerda que antes no culminaste:
{{implode("\n", $otherSapCoursesUnfinishedNames)}}  
    @endif
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@php
$tmpFlag = false;
foreach ($otherSapCourses as $course):
    if ($course["course_status"] == "REPROBADO" || $course["course_status"] == "ABANDONADO" || $course["course_status"] == "NO CULMINÓ"):
        $tmpFlag = true;
    endif;
endforeach;

$tmpShowSapSectionFlag = ($tmpFlag || count($otherFreeCoursesDissaprovedNames) > 0 || count($otherFreeCoursesDroppedNames) > 0 || count($otherFreeCoursesUnfinishedNames) > 0) ? true : false;

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


{{-- Filas 326 a 332: si en la columna SAP es UN solo curso --}}
@if((count($sapCourses) + count($otherSapCourses))== 1)
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

if(
    count($otherSapCoursesDissaprovedNames) > 0 // curso SAP reprobado
    && count($otherSapCoursesToEnableNames) == 0 // no hay cursos SAP por habilitar
    && ( // hay cursos de obsequio cursando o por habilitar o aprobados
        count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
        || count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
        || count($otherFreeCoursesApprovedNames) > 0 // hay cursos de obsequio aprobados
    )
    ):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haber reprobado {{implode(', ', $otherSapCoursesDissaprovedNames)}} y no haberte certificado en {{implode(', ', $sapCoursesNames)}}:
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


{{-- Filas Filas 340 a 346: si se utiliza la fila 308 (ABANDONASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 308 y el nombre del curso de la fila 290 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;

if(
    count($otherSapCoursesDroppedNames) > 0 // curso SAP abandonado
    && count($otherSapCoursesToEnableNames) == 0 // no hay cursos SAP por habilitar
    && ( // hay cursos de obsequio cursando o por habilitar o aprobados
        count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
        || count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
        || count($otherFreeCoursesApprovedNames) > 0 // hay cursos de obsequio aprobados
    )
    ):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haber abandonado {{implode(', ', $otherSapCoursesDissaprovedNames)}} y no haberte certificado en {{implode(', ', $sapCoursesNames)}}:
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



{{-- Filas 347 a 353: si se utiliza la fila 309 (NO CULMINASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 309 y el nombre del curso de la fila 290 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;

if(
    count($otherSapCoursesUnfinishedNames) > 0 // curso SAP no culminados
    && count($otherSapCoursesToEnableNames) == 0 // no hay cursos SAP por habilitar
    && ( // hay cursos de obsequio cursando o por habilitar o aprobados
        count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
        || count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
        || count($otherFreeCoursesApprovedNames) > 0 // hay cursos de obsequio aprobados
    )
    ):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
Por lo que, al no haber culminado {{implode(', ', $otherSapCoursesDissaprovedNames)}} y no haberte certificado en {{implode(', ', $sapCoursesNames)}}:
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



{{--  Filas 354 a 362: si se utiliza la fila 306 (APROBASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 306 y el nombre del curso de la fila 290 y NO tiene más cursos SAP por habilitar --}}
@php
$tmpShowSectionFlag = false;
$tmpShowToEnableFlag = false;

if(
    count($otherSapCoursesCertifiedNames) > 0 // curso SAP certificados (los de "apobaste")
    && count($otherSapCoursesToEnableNames) == 0 // no hay cursos SAP por habilitar
    && ( // hay cursos de obsequio cursando o por habilitar o aprobados
        count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
        || count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
        || count($otherFreeCoursesApprovedNames) > 0 // hay cursos de obsequio aprobados
    )
    ):
    $tmpShowSectionFlag = true;
endif;
if(
    $tmpShowSectionFlag == true
    && count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
    &&       count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
    ):
    $tmpShowToEnableFlag = true;
endif;

@endphp
@if ($tmpShowSectionFlag)
Por lo que, al haberte certificado anteriormente en {{implode(', ', $otherSapCoursesCertifiedNames)}}, aunque no te certificaste en {{implode(', ', $sapCoursesNames)}}:
    @if(count($otherFreeCoursesInProgressNames) > 0)
    Puedes seguir *cursando:*
{{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    Aún puedes *habilitar:*
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif    

    @if($tmpShowToEnableFlag == true)
    Pero ten en cuenta que si no te certificas en este curso, pierdes automáticamente la posibilidad de habilitar:
{{implode("\n", $otherFreeCoursesToEnableNames)}}
    Porque no puedes tener más de 2 cursos reprobados o abandonados.
    @endif    
    @if($tmpShowToEnableFlag == false && count($otherFreeCoursesToEnableNames) > 0)
        @if(count($otherFreeCoursesToEnableNames) == 1)
        A continuación te envío las fechas de inicio para habilitarlo:
        @else
        A continuación te envío las fechas de inicio para habilitarlos:
        @endif
        @foreach ($toEnableFreeCoursesDates as $date)
{{$date->format('d/m/Y')}}
        @endforeach

        Tienes como máximo una semana para escoger al menos la última fecha de inicio, posterior a ella, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.
    @endif
@endif


{{-- Filas 370 a 382: si se usan las filas 307 a 309. Fila 370: si tiene UN curso SAP como PENDIENTE en la columna de ESTADO. Colocar en los "()" el nombre del curso o cursos SAP que esten como reprobados, abandonados o no certificados, incluyendo la fila 290 --}}
@php
$tmpShowSectionFlag = false;

if(
    count($otherSapCoursesToEnableNames) > 0 // si hay cursos SAP por habilitar
    &&
        (
        count($otherSapCoursesDissaprovedNames) > 0 // curso SAP reprobado
        || count($otherSapCoursesDroppedNames) > 0 // curso SAP abandonado
        || count($otherSapCoursesUnfinishedNames) > 0 // curso SAP no culminado
        )
    ):
    $tmpShowSectionFlag = true;
endif;

//  nombre del curso o cursos SAP que esten como reprobados, abandonados o no certificados
$tmpIrregularSapCoursesNames = array_merge($sapCoursesNames, $otherSapCoursesDissaprovedNames, $otherSapCoursesDroppedNames, $otherSapCoursesUnfinishedNames);
$tmpIrregularSapCoursesNames2 = array_merge($otherSapCoursesDissaprovedNames, $otherSapCoursesDroppedNames, $otherSapCoursesUnfinishedNames);

@endphp
@if ($tmpShowSectionFlag)
    @if(count($otherSapCoursesCertifiedNames) == 0){{-- si hay curso SAP aprobado anteriormente --}}
        @if(count($otherSapCoursesToEnableNames) == 1)
        Por lo que, al no haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames)}}, tienes como máximo 15 días para iniciar con el siguiente curso SAP ofrecido:
        @elseif(count($otherSapCoursesToEnableNames) > 1)
        Por lo que, al no haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames)}}, tienes como máximo 15 días para iniciar con los siguientes cursos SAP ofrecidos:
        @endif
    @else
        @if(count($otherSapCoursesToEnableNames) == 1)
        Por lo que, al haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames2)}}, aunque no te hayas certificado en {{implode(', ', $sapCoursesNames)}}, puedes iniciar como máximo en 15 días con el siguiente curso SAP ofrecido:
        @elseif(count($otherSapCoursesToEnableNames) > 1)
        Por lo que, al haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames2)}}, aunque no te hayas certificado en {{implode(', ', $sapCoursesNames)}}, puedes iniciar como máximo en 15 días con los siguientes cursos SAP ofrecidos:
        @endif
    @endif
    {{implode("\n", $otherSapCoursesToEnableNames)}}
    @if(count($otherSapCoursesToEnableNames) == 1)
    Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, lo estarás perdiendo.
    A continuación te envío las fechas de inicio para habilitarlo:
    @else
    Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.
    A continuación te envío las fechas de inicio para habilitarlos:
    @endif
    @foreach ($toEnableSapCoursesDates as $date)
{{$date->format('d/m/Y')}}
    @endforeach
@endif