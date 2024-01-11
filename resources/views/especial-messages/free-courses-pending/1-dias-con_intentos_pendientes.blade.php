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


{{-- Variante si es PBI o MSP --}}
@if ($hasSpecializedCoursesToNotify == true && $hasExcelCourseToNotify == false)
Asimismo, veo que no aprobaste tu examen de certificación, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación.
{{-- Variante si es PBI o MSP con Excel--}}
{{-- Variante si es solo Excel--}}
@else
Asimismo, veo que no aprobaste tus exámenes de certificación, por lo que *no te estarás certificando,* ya que no brindamos certificados solo por participación, ni por niveles independientes de Excel.
@endif


Si en dado caso realizas los intentos de examen fuera de mi horario laboral, no aceptaremos capturas de pantalla de tu aprobación para apoyarte con el certificado, *sin excepciones.*

⏳ *¡Actúa ya!* Paga 3 meses de aula virtual HOY o como máximo en 1 semana. 
De igual manera te comento que al realizar el pago, el aula virtual que recibirás estará completamente vacía porque tu avance se habría eliminado.

@if(count($coursesToNotify) == 1)
Si por el contrario, deseas retomar el curso *pasada la semana indicada, tendrás que volver a matricularte con el precio regular.* Recordando que este curso te los ofrecimos como obsequio.

@else
Si por el contrario, deseas retomar algún curso *pasada la semana indicada, tendrás que volver a matricularte con el precio regular.* Recordando que estos cursos te los ofrecimos como obsequio.

@endif

⚠️ *Importante: Pagos fuera de mi horario laboral no serán reconocidos. No habrá reembolsos, tendrá que completar el valor faltante de ser el caso.*

*Ha sido una lástima no contar con tu participación en esta certificación.* Ahora te comento sobre los demás cursos:


{{-- VARIANTE Filas 36 a 40: si tiene curso obsequio con estado CURSANDO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@if($showInProgressOtherCourses == true)

👀 *OJO también estabas cursando:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'CURSANDO')
{{$course['name']}}
        @endif
    @endforeach

    @if(count($coursesToNotify) == 1)
        @if (count(array_filter($otherFreeCourses, function ($course) {    return  ($course['course_status'] === 'CURSANDO'); })))
        Al haber reprobado/abandonado solo el curso mencionado anteriormente, puedes conservar este acceso. Teniendo en cuenta que al tener dos o más cursos reprobados/abandonados, pierdes el resto de los cursos. Si tienes dudas sobre esto, contáctame.

        @else
        Al haber reprobado/abandonado solo el curso mencionado anteriormente, puedes conservar estos accesos. Teniendo en cuenta que al tener dos o más cursos reprobados/abandonados, pierdes el resto de los cursos. Si tienes dudas sobre esto, contáctame.

        @endif
    @else // count($coursesToNotify) > 1
    Y como condición, no podías tener dos o más cursos reprobados y has reprobado/abandonado:
    {{$course['name']}}

    {{-- Fila 609: solo si en la fila 582 son dos cursos y si en ESTADO AULA de SAP dice CURSANDO o COMPLETA pero en certificado aún no sale EMITIDO --}}
        @if(count($coursesToNotify) == 2 && ($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETA") && ($studentData["CERTIFICADO"] != "EMITIDO"))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y aún no te certificas en SAP.
    {{-- Fila 610: solo si en la fila 582 es un curso y si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
        @elseif((count($coursesToNotify) == 1 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
Recuerda que como condición no puedes tener dos o más cursos *reprobados o abandonados,* y no lograste certificarte en SAP. Por lo que está en peligro este curso, si no te certificas en:
        {{-- Fila 611: solo si en la fila 582 son dos cursos y si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS --}}
        @elseif(count($coursesToNotify) == 2 && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
        Dando un total de 3 cursos reprobados/abandonados, por lo que pierdes automáticamente el acceso a este curso que aún no culminabas, porque también reprobaste/abandonaste SAP.
        @endif
    @endif
@endif


{{-- VARIANTE Filas 43 a 55: si tiene curso obsequio con estado examen SIN INTENTOS PENDIENTES o REPROBADO, que termine en OTRA FECHA, con las condiciones específicas de cada fila: --}}
@php 
$tmpDissaprovedOtherCourses = array_filter($otherFreeCourses, function ($course) {    return  ($course['course_status'] === 'REPROBADO'); });
@endphp
@if($showDissaprovedOtherCourses == true )
👀 *OJO recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados y al haber reprobado anteriormente:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'REPROBADO')
{{$course['name']}}
        @endif
    @endforeach

    Y no haberte certificado en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($coursesToNotify) == 1 && count($tmpDissaprovedOtherCourses) == 1)
    Tienes un total de 2 cursos reprobados/abandonados.
    @elseif((count($coursesToNotify) == 1 + count($tmpDissaprovedOtherCourses)) == 3)
    Tienes un total de 3 cursos reprobados/abandonados.
    {{-- Fila 620: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 2 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpDissaprovedOtherCourses))  == 2 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 3 cursos reprobados/abandonados, porque también reprobaste SAP.
    {{-- Fila 621: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 3 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpDissaprovedOtherCourses))  == 3 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 4 cursos reprobados/abandonados, porque también reprobaste SAP.
    @endif

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'POR HABILITAR')
A pesar de quedar pendiente, no podrás habilitar:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'CURSANDO')
A pesar de haber iniciado, perderías el acceso a:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'APROBADO')
A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{$course['name']}}        
        @endif
    @endforeach    

Siendo este tu último procedimiento con nosotros, porque no tendrías más cursos que habilitar.
En este caso, al no tener más cursos de obsequio pendientes, quedaremos en contacto por tu curso SAP, el cual finaliza el día:
{{$endCourseDate->format('d/m/Y')}}
@endif

{{-- VARIANTE Filas 70 a 82: Filas 70 a 94: si tiene curso obsequio con estado NO CULMINÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@php 
$tmpUnfinishedOtherCourses = array_filter($otherFreeCourses, function ($course) {    return  ($course['course_status'] === 'NO CULMINÓ'); });
@endphp
@if($showUnfinishedOtherCourses == true )
👀 *OJO recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados y al no haber culminado:*

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'NO CULMINÓ')
{{$course['name']}}
        @endif
    @endforeach

    Y no haberte certificado en:
    @foreach ($coursesToNotify as $course)
{{$course['name']}}
    @endforeach    

    @if(count($coursesToNotify) == 1 && count($tmpUnfinishedOtherCourses) == 1)
    Tienes un total de 2 cursos reprobados/abandonados.
    @elseif((count($coursesToNotify) == 1 + count($tmpUnfinishedOtherCourses)) == 3)
    Tienes un total de 3 cursos reprobados/abandonados.
    {{-- Fila 620: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 2 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpUnfinishedOtherCourses))  == 2 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 3 cursos reprobados/abandonados, porque también reprobaste SAP.
    {{-- Fila 621: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 3 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpUnfinishedOtherCourses))  == 3 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 4 cursos reprobados/abandonados, porque también reprobaste SAP.
    @endif    

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'POR HABILITAR')
        Por lo que, a pesar de quedar pendiente, no podrás habilitar:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'CURSANDO')
A pesar de haber iniciado, perderías el acceso a:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'APROBADO')
A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{$course['name']}}        
        @endif
    @endforeach    


    Siendo este tu último procedimiento con nosotros, porque no tendrías más cursos que habilitar.
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
        En este caso, al no tener más cursos de obsequio pendientes, quedaremos en contacto por tu curso SAP, el cual finaliza el día:
        {{$endCourseDate->format('d/m/Y')}}
    @endif

@endif


{{-- VARIANTE Filas Filas 97 a 121: si tiene curso obsequio con estado ABANDONÓ, que termine en OTRA FECHA, si tuviera fecha fin, con las condiciones específicas de cada fila: --}}
@php 
$tmpDroppedOtherCourses = array_filter($otherFreeCourses, function ($course) {    return  ($course['course_status'] === 'ABANDONÓ'); });
@endphp
@if($showDroppedOtherCourses == true )
👀 *OJO recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados y al haber abandonado:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'ABANDONADO')
{{$course['name']}}
        @endif
    @endforeach

    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
    Y no haberte certificado en:
        @foreach ($coursesToNotify as $course)
{{$course['name']}}
        @endforeach  
    @endif

    @if(count($coursesToNotify) == 1 && count($tmpDroppedOtherCourses) == 1)
    Tienes un total de 2 cursos reprobados/abandonados.
    @elseif((count($coursesToNotify) == 1 + count($tmpDroppedOtherCourses)) == 3)
    Tienes un total de 3 cursos reprobados/abandonados.
    {{-- Fila 620: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 2 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpDroppedOtherCourses))  == 2 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 3 cursos reprobados/abandonados, porque también reprobaste SAP.
    {{-- Fila 621: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 3 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpDroppedOtherCourses))  == 3 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 4 cursos reprobados/abandonados, porque también reprobaste SAP.
    @endif        

    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'POR HABILITAR')
        Por lo que, a pesar de quedar pendiente, no podrás habilitar:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'CURSANDO')
A pesar de haber iniciado, perderías el acceso a:
{{$course['name']}}        
        @elseif ($course['course_status'] == 'APROBADO')
A pesar de haber aprobado, perderías el acceso al certificado internacional:
{{$course['name']}}        
        @endif
    @endforeach    

    Siendo este tu último procedimiento con nosotros, porque no tendrías más cursos que habilitar.
    @if(($studentData["AULA SAP"] == "CURSANDO" || $studentData["AULA SAP"] == "COMPLETADO") && ($studentData["CERTIFICADO"] != "EMITIDO") )
        En este caso, al no tener más cursos de obsequio pendientes, quedaremos en contacto por tu curso SAP, el cual finaliza el día:
        {{$endCourseDate->format('d/m/Y')}}
    @endif
@endif


{{-- VARIANTE Filas 124 a 144: si tiene curso obsequio con estado POR HABILITAR, con las condiciones específicas de cada fila: --}}
@php 
$tmpToEnableOtherCourses = array_filter($otherFreeCourses, function ($course) {    return  ($course['course_status'] === 'POR HABILITAR'); });
@endphp

@if($showToEnableOtherCourses == true )
👀 *OJO tienes por habilitar:*
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'POR HABILITAR')
{{$course['name']}}
        @endif
    @endforeach

    @if(count($coursesToNotify) == 1)
    A continuación te envío las fechas de inicio disponibles, *teniendo en cuenta que si no escoges una de ellas como máximo hasta el día de la última fecha enviada, los estarías perdiendo:*

    @elseif(count($coursesToNotify) == 1 && count($tmpToEnableOtherCourses) == 1)
    Tienes un total de 2 cursos reprobados/abandonados.
    @elseif((count($coursesToNotify) == 1 + count($tmpToEnableOtherCourses)) == 3)
    Tienes un total de 3 cursos reprobados/abandonados.
    {{-- Fila 620: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 2 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpToEnableOtherCourses))  == 2 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 3 cursos reprobados/abandonados, porque también reprobaste SAP.
    {{-- Fila 621: Si en ESTADO EXAMEN de SAP dice REPROBADO o SIN INTENTOS GRATIS y en la suma de las filas 615 y 617 el resultado es 3 cursos. --}}
    @elseif(((count($coursesToNotify) + count($tmpToEnableOtherCourses))  == 3 ) && (($studentData["EXAMEN"] == "REPROBADO") || (stripos($studentData["EXAMEN"], 'Sin intentos Gratis') !== false)))
    Tienes un total de 4 cursos reprobados/abandonados, porque también reprobaste SAP.
    @endif        
    
    @foreach ($otherFreeCourses as $course)
        @if ($course['course_status'] == 'CURSANDO')
Por lo que, a pesar de haber iniciado, perderías el acceso a:
{{$course['name']}}
        @elseif ($course['course_status'] == 'APROBADO')
Por lo que, a pesar de haber aprobado, perderías el acceso al certificado internacional:
{{$course['name']}}
        @endif
    @endforeach

@endif
