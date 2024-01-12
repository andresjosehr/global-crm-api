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
    @if(count($otherSapCoursesToEnableNames) > 0)
    Recuerda que tienes por habilitar:
    {{implode("\n", $otherSapCoursesToEnableNames)}}  
    @endif
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@php
// Filas 361 a 373: si se utilizan las filas 355, 356, 357 y/o 358. También si se utiliza la fila 354 CON alguna de las filas desde 355 a 357. 
$tmpFlag = false;
if(
        count($otherSapCoursesDissaprovedNames) > 0 || 
        count($otherSapCoursesDroppedNames) > 0 || 
        count($otherSapCoursesUnfinishedNames) > 0 ||
        count($otherSapCoursesToEnableNames) > 0
        ):
    $tmpFlag = true;
endif;
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
@php
// Filas 375 a 381: si en la columna SAP es UN solo curso
$tmpShowSectionFlag = ((count($sapCourses) + count($otherSapCourses))== 1);

@endphp
@if($tmpShowSectionFlag)
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



@php 
// Filas 382 a 392: fila 382: si se utiliza la fila 358 y en la columna ESTADO es UN curso SAP con estado PENDIENTE
$tmpShowSectionFlag = (count($otherSapCoursesToEnableNames) > 0);
@endphp
@if($tmpShowSectionFlag)
        @if(count($otherSapCoursesToEnableNames) == 1)
        Por lo que, *al tener aún un curso SAP pendiente por habilitar:*
        @else
        Por lo que, *al tener aún más de un curso SAP pendiente por habilitar:*
        @endif
        @if(count($otherFreeCoursesInProgressNames) > 0)
        *Para mantener el acceso al siguiente curso:*
    {{implode("\n", $otherFreeCoursesInProgressNames)}}
    @endif
    @if(count($otherFreeCoursesToEnableNames) > 0)
    *Para poder habilitar:*
    {{implode("\n", $otherFreeCoursesToEnableNames)}}
    @endif
    @if(count($otherFreeCoursesApprovedNames) > 0)
    *Para tener el certificado del curso que aprobaste:*
{{implode("\n", $otherFreeCoursesApprovedNames)}}
    @endif
    @if(count($sapCourses) == 1)
    Debes iniciar y aprobar el curso SAP mencionado anteriormente, porque no puedes tener más de 2 cursos reprobados o abandonados.
    @else
    Debes iniciar y aprobar al menos uno de los cursos SAP mencionados anteriormente, porque no puedes tener más de 2 cursos reprobados o abandonados.
    @endif
@endif



@php
// Filas 393 a 399: si se utiliza la fila 355. En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 355 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar

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


@php
// Filas 400 a 406: si se utiliza la fila 356. En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 356 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar

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
Por lo que, al haber abandonado {{implode(', ', $otherSapCoursesDroppedNames)}} y no haberte certificado en {{implode(', ', $sapCoursesNames)}}:
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

@php
// Filas 407 a 413: si se utiliza la fila 357. En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 357 y el nombre del curso de la fila 341 y NO tiene más cursos SAP por habilitar

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
Por lo que, al no haber culminado {{implode(', ', $otherSapCoursesUnfinishedNames)}} y no haberte certificado en {{implode(', ', $sapCoursesNames)}}:
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

@php
// Filas 354 a 362: si se utiliza la fila 306 (APROBASTE). En los "()" colocar el nombre del curso o cursos SAP que se encuentran en la columna de OBSERVACIONES con los estados de la fila 306 y el nombre del curso de la fila 290 y NO tiene más cursos SAP por habilitar 
$tmpShowSectionFlag = false;

if(
    count($otherSapCoursesCertifiedNames) > 0 // curso SAP certificados (los de "apobaste")
    && count($otherSapCoursesToEnableNames) == 0 // no hay cursos SAP por habilitar
    && ( // hay cursos de obsequio cursando o por habilitar o aprobados
        count($otherFreeCoursesInProgressNames) > 0 // hay cursos de obsequio en progreso
        || count($otherFreeCoursesToEnableNames) > 0 // hay cursos de obsequio por habilitar
    )
    ):
    $tmpShowSectionFlag = true;
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
@endif

@php
// Filas 420 a 426: solo si se usan las filas 358 a 359 y en la columna ESTADO aparezca PENDIENTE uno o más cursos SAP. Fila 420: si es un solo curso en la fila 359 y/o 371. *Colocar el nombre del próximo curso a habilitar, teniendo en cuenta que si tiene más cursos SAP, se le da prioridad a SAP, si no tiene más cursos SAP, se da prioridad a los cursos obsequio que tenga por habilitar si se usan las filas 414 a 418 (es decir al menos un curso SAP con estado CERTIFICADO).
$tmpShowSectionFlag = false;

if(
    count($otherSapCoursesToEnableNames) > 0 // hay cursos SAP por habilitar!!
    ):
    $tmpShowSectionFlag = true;
endif;
@endphp
@if ($tmpShowSectionFlag)
    @if(count($otherSapCoursesToEnableNames) == 1)
    A continuación te envío las fechas de inicio para habilitar el siguiente curso: {{implode(', ', $otherSapCoursesToEnableNames)}}:
    @else
    A continuación te envío las fechas de inicio para habilitar los siguientes cursos: {{implode(', ', $otherSapCoursesToEnableNames)}}:
    @endif
    @foreach ($otherSapCoursesToEnableNames as $date)
{{$date->format('d/m/Y')}}
    @endforeach

Tienes como *máximo 15 días* para escoger al menos la última fecha de inicio, posterior a ella, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.
@endif


@php
// Filas 427 a 431: si se usan las filas 355 a 357 (reprobaste, abandonaste no culminaste). 
// Fila 427: si tiene UN curso SAP como PENDIENTE en la columna de ESTADO. Colocar en los "()" el nombre del curso o cursos SAP que esten como reprobados, abandonados o no certificados, incluyendo la fila 341

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
    @if(count($otherSapCoursesDissaprovedNames) > 0 ||  count($otherSapCoursesDroppedNames) > 0 || count($otherSapCoursesUnfinishedNames) > 0)
        @if(count($otherSapCoursesToEnableNames) == 1)
    Por lo que, al no haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames)}}, tienes como máximo 15 días para iniciar con el siguiente curso SAP ofrecido:
        @elseif(count($otherSapCoursesToEnableNames) > 1)
    Por lo que, al no haberte certificado en {{implode(', ', $tmpIrregularSapCoursesNames)}}, tienes como máximo 15 días para iniciar con los siguientes cursos SAP ofrecidos:
        @endif
    @endif
    @if(count($otherSapCoursesCertifiedNames) > 0 )
        @if(count($otherSapCoursesToEnableNames) == 1)
        Por lo que, al haberte certificado en {{implode(', ', $otherSapCoursesCertifiedNames)}}, aunque no te hayas certificado en {{implode(', ', $sapCoursesNames)}}, puedes iniciar como máximo en 15 días con el siguiente curso SAP ofrecido:
        @elseif(count($otherSapCoursesToEnableNames) > 1)
        Por lo que, al haberte certificado en {{implode(', ', $otherSapCoursesCertifiedNames)}}, aunque no te hayas certificado en {{implode(', ', $sapCoursesNames)}}, puedes iniciar como máximo en 15 días con los siguientes cursos SAP ofrecidos:
        @endif
    @endif
    {{implode("\n", $otherSapCoursesToEnableNames)}}
@endif
