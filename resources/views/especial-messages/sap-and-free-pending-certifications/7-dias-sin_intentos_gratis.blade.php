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
FALTANDO 7 dias PARA LA FECHA FIN DEL CURSO

CURSO SAP Y OBSEQUIOS
ESTADO DE EXAMEN: SIN INTENTOS GRATIS
FECHA DE FIN DE CURSO: 7 dias hacia delante

--}}
{{$student_name}}
⚠️ ¡Atención inmediata y crítica para ti! ⏳


Están por vencer tus cursos:
@foreach ($coursesToNotify as $course)
{{$course['name']}}
@endforeach

{{-- Variante para SIN INTENTOS GRATIS --}}
*Tu futuro está en juego y no hemos concretado ningún pago de las opciones brindadas anteriormente.* 💼🚀

🙌 Te comento que *no todo está perdido,* porque hemos conseguido una última opción para ti:
@if( count($freeCourses) == 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tu curso de obsequio, a un precio super especial.
@elseif ( count($freeCourses) > 1)
🚨 Puedes pagar para *PONDERAR* todos los resultados de tus exámenes + el avance académico completado en tu aula virtual de SAP y de tus cursos de obsequio, a un precio super especial.
@endif


*Posterior a tu pago, en máximo 48 horas hábiles tendrás el certificado y la insignia digital respaldada por Credly, ya que estarías aprobando SAP.*
📌 *¡No pierdas más tiempo y realiza el pago en este momento!* Ya que, si esperas a los próximos días, perderás esta posibilidad.

*Responde inmediatamente. Tu futuro está en juego.* 💼🚀 Y la fecha de fin de los cursos es el día:
{{$endCourseDate->format('d/m/Y')}}

{{-- hay curso de Excel? --}}
@if ($excelCourseFlag == false)
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. ¡Persiste y alcanza tus metas! 🌟
@else
🚨 Recuerda que no emitimos certificados por completar los cursos o simplemente participar. Además, no otorgamos certificados por niveles individuales de Excel. ¡Persiste y alcanza tus metas! 🌟
@endif
No dejes que el tiempo se agote⏳. *Actúa ahora y asegúrate de mantener tu camino hacia la certificación.*

{{-- SUBPLANTILLA: Cursos SAP anteriores --}}
@include('especial-messages.sap-and-free-pending-certifications.__other_sap_courses_section', ['sapCourses' => $sapCourses, 'otherSapCourses' => $otherSapCourses, 'otherFreeCourses' => $otherFreeCourses])

⚠️ Recuerda que el día de tu fecha de fin, se eliminarán tus accesos de manera automática a las 23:59. 
*Aprovecho para comentarte que toda solicitud y pagos, deben ser dentro de mi horario laboral: Lun-Vier 9:00am a 7:00pm y Sáb. 9:00am a 5:00pm (HORA PERÚ).* Asimismo, que no habrán devoluciones de no cumplir con el pago que corresponda en el plazo indicado anteriormente.

Quedo al pendiente de tu respuesta y si necesitas apoyo para realizar tu pago.
