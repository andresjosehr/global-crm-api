{{--

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 1 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 1 dias hacia delante

--}}
*¡Tenemos noticias lamentables! Si no recibimos respuestas de tu parte:*
{{$student_name}}

Te envío la última información de tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

@if ($endCourseDate->isToday())
🚨 *Hoy a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@elseif ($endCourseDate->isTomorrow())
🚨 *Mañana a las 23:59, tu aula virtual será eliminada,* es decir, que se perderán todos los avances realizados, no pudiendo ser recuperados luego.
@endif

{{-- Variante para SIN INTENTOS GRATIS --}}
Por lo que *estarías perdiendo la posibilidad de realizar el pago por el ponderado* de los exámenes de certificación de los cursos, si no realizas el pago, *dentro de mi horario laboral del día de hoy.*
*Ojo, si esperas al último minuto de mi jornada laboral de hoy, no podré validar, ni solicitar tu certificado, tampoco realizamos devoluciones.*


{{-- Cursos SAP anteriores --}}
@if ($showOlderSapCoursesFlag == true)
@foreach ($olderSapCourses as $course)
Recuerda que antes {{$course['statusToDisplay']}}:
{{$course['name']}}
@endforeach
@endif

{{-- Cursos de obsequio: SECCION ESPECIAL si el curso SAP anterior fue reprobado, abandonado o no lo culminó --}}
@if ($showOtherFreeCoursesFlag == true)
👀 OJO, como condición, no puedes tener dos o más cursos reprobados/abandonados, por lo que sobre *tus cursos de obsequio te comento:*
@foreach ($otherFreeCourses as $course)
@if ($course['status'] == 'CURSANDO')
Aún estás *cursando:*
@elseif ($course['status'] == 'REPROBADO')
Completaste pero *REPROBASTE:*
@elseif ($course['status'] == 'NO CULMINÓ')
*No culminaste:*
@elseif ($course['status'] == 'ABANDONADO')
*Abandonaste:*
@elseif ($course['status'] == 'POR HABILITAR')
Aún tienes *por habilitar:*
@elseif ($course['status'] == 'APROBADO')
*Aprobaste:*
@endif
{{$course['name']}}
@endforeach
@endif

{{-- Advertencia por cursos SAP anteriores y solo es 1 curso SAP actual --}}
@if ($showWarningSapCourseCertificationFlag == true && $multipleSapCoursesFlag == false)
Por lo que, *al no haberte certificado en SAP, que es el curso principal:*
{{-- Advertencia por cursos SAP anteriores y cursos SAP reprobados --}}
@elseif ($showWarningSapCourseCertificationFlag == true && $showNoticeDissaprovedSapCourses == true)
Por lo que, al haber reprobado ({{$noticeDisapprovedSapCourseNames}}) y no haberte certificado en ({{$sapCourseNames}}):
{{-- Advertencia por cursos SAP anteriores y cursos SAP abandonados --}}
@elseif ($showWarningSapCourseCertificationFlag == true && $showNoticeDroppedOlderSapCourses == true)
Por lo que, al haber abandonado ({{$noticeDroppedSapCourseNames}}) y no haberte certificado en ({{$sapCourseNames}}):
{{-- Advertencia por cursos SAP anteriores y cursos SAP no culminados --}}
@elseif ($showWarningSapCourseCertificationFlag == true && $showNoticeUnfinishedOlderSapCourses == true)
Por lo que, al no haber culminado ({{$noticeUnfinishedSapCoursesNames}}) y no haberte certificado en ({{$sapCourseNames}}):
@endif
{{-- mostrar cursos obsequios de advertencia --}}
@if ($showWarningSapCourseCertificationFlag == true && ($multipleSapCoursesFlag == false || $showNoticeDissaprovedSapCourses == true || $showNoticeDroppedOlderSapCourses == true || $showNoticeUnfinishedOlderSapCourses == true))
    @foreach ($otherFreeCourses as $course)
        @if ($course['status'] == 'CURSANDO')
A pesar de haberlo habilitado, pierdes el acceso a:
{{$course['name']}}
        @elseif ($course['status'] == 'POR HABILITAR')
Pierdes la posibilidad de iniciar:
{{$course['name']}}
        @elseif ($course['status'] == 'APROBADO')
Y a pesar de haber aprobado, pierdes el certificado internacional de:
{{$course['name']}}
        @endif
    @endforeach
@endif

{{-- Advertencia por cursos SAP anteriores y cursos SAP aprobados --}}
@if ($showWarningSapCourseCertificationFlag == true && $showNoticeApprovedOlderSapCourses == true)
Por lo que, al haberte certificado anteriormente en ({{$noticeApprovedSapCourseNames}}), aunque no te certificaste en ({{implode(', ', $sapCoursesNames)}}), puedes iniciar como máximo en 15 días con el siguiente curso SAP ofrecido:
    @foreach ($otherFreeCourses as $course)
        @if ($course['status'] == 'CURSANDO')
Puedes seguir *cursando:*
{{$course['name']}}
        @elseif ($course['status'] == 'POR HABILITAR')
Aún puedes *habilitar:*
{{$course['name']}}

Pero ten en cuenta que si no te certificas en este curso, pierdes automáticamente la posibilidad de habilitar:
{{$course['name']}}
Porque no puedes tener más de 2 cursos reprobados o abandonados.
        @endif
    @endforeach
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

@if (count($pendingSapCoursesNames) == 1)
Por lo que, al no haberte certificado en ({{implode(', ', $pendingSapCoursesNames)}}), tienes como máximo 15 días para iniciar con el siguiente curso SAP ofrecido:
@elseif (count($pendingSapCoursesNames) > 1)
Por lo que, al no haberte certificado en ({{implode(', ', $pendingSapCoursesNames)}}), tienes como máximo 15 días para iniciar con los siguientes cursos SAP ofrecidos:
@elseif (count($noticeApprovedSapCourseNames) >= 1 && $multipleSapCoursesFlag == false)
Por lo que, al haberte certificado en ({{implode(', ', $noticeApprovedSapCourseNames)}}), aunque no te hayas certificado en ({{$sapCourseNames}}), puedes iniciar como máximo en 15 días con el siguiente curso SAP ofrecido:
@elseif (count($noticeApprovedSapCourseNames) >= 1 && $multipleSapCoursesFlag == true)
Por lo que, al haberte certificado en ({{implode(', ', $noticeApprovedSapCourseNames)}}), aunque no te hayas certificado en ({{$sapCourseNames}}), puedes iniciar como máximo en 15 días con los siguientes cursos SAP ofrecidos:
@endif
{{implode('\n', $sapCoursesNames) }}
@if (count($pendingSapCoursesNames) >= 1)
    {{-- Filas 375 a 376 --}}
    @if (count($pendingSapCoursesNames) == 1)
Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, lo estarás perdiendo.
    @else
Posterior a estos 15 días, como te hemos indicado en tu ficha de matrícula y confirmación de compra, los estarás perdiendo.
    @endif

    @if (count($sapCoursesNames) == 1) 
A continuación te envío las fechas de inicio para habilitarlo:
    @else
A continuación te envío las fechas de inicio para habilitarlos:
    @endif
    @foreach ($toEnableSapCoursesDates as $date)
{{$date->format('d/m/Y')}}
    @endforeach
@endif


*Lamentamos no contar con tu participación en la certificación de Key User SAP.*

*Te recuerdo por última vez que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* No habrán devoluciones de ningún tipo, si el pago es enviado fuera de mi horario, así sea por un minuto. 

Saludos cordiales.
