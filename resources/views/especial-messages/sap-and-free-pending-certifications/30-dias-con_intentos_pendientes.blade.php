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

"PLANTILLAS CURSO SAP Y OBSEQUIOS CON INTENTOS PENDIENTES"
FALTANDO 1 MES PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 1 mes hacia delante

--}}
¡Hola!
{{$student_name}}

Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para INTENTOS PENDIENTES --}}
@php
$pendingCoursesToNotifyNames = [];
$hasSapNoFreeAttempts = false;
$freeCourseNoFreeAttemptsCount = 0;
foreach ($coursesToNotify as $course):
    if($course['noFreeAttempts'] == true && $course['isSapCourse'] == true):
        $hasSapNoFreeAttempts = true;
    endif;
    if($course['noFreeAttempts'] == true && $course['isFreeCourse'] == true):
        $freeCourseNoFreeAttemptsCount++;
    endif;
    if ($course['hasPendingAttempts'] == true):
        if($course['isExcelCourse'] == false):
            $pendingCoursesToNotifyNames[] = $course['name'];
        else:
            foreach($course['LEVELS'] as $level):
                $pendingCoursesToNotifyNames[] = $course[$level]['name'];
             endforeach;
        endif;
    endif;
endforeach;
@endphp
🤓 Te comento sobre el avance académico que tienes actualmente en cada curso:
{{-- SUBPLANTILLA: Avances de lecciones  --}}@include('especial-messages.sap-and-free-pending-certifications.__lessons_for_courses_section', ['coursesToNotify' => $coursesToNotify])

{{-- Variante para INTENTOS PENDIENTES --}}
🙌 Si no crees poder certificarte en el tiempo que te queda disponible, *NO TODO ESTÁ PERDIDO:*
🚩 Puedes *EXTENDER el tiempo* y mantener los beneficios que tienes ahora.

Si te interesa tomar esta opción, *te recomiendo realizar el pago en este momento,* ya que, *una semana antes de la fecha de fin,* no estará disponible la extensión de 1 mes.* Y tendrás que ajustarte a las nuevas condiciones de extensión.
Por favor me indicas si te interesa *y no perder el tiempo y el dinero que has invertido.*

📌 Recuerda que estos cursos finalizan el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación.
@else
🚨 Para poder certificarte debes aprobar los exámenes de certificación, porque no emitimos certificado por haber completado el curso, ni por participación. Tampoco emitimos certificado por nivel independiende de Excel.
@endif

{{-- SUBPLANTILLA: Cursos SAP anteriores --}}
@include('especial-messages.sap-and-free-pending-certifications.__other_sap_courses_section', ['sapCourses' => $sapCourses, 'otherSapCourses' => $otherSapCourses, 'otherFreeCourses' => $otherFreeCourses])


⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.