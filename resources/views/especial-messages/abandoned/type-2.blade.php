@php
    $sapCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'paid';
    });
    $sapCourses = array_values($sapCourse)[0];
    $inactiveSapCourses = $student['inactive_courses']
    $allCourses = array_merge($sapCourse, $inactiveSapCourse);
@endphp

Lamentamos no contar con tu participación en la certificación como KEY USER SAP.

En este caso, te recuerdo que inicialmente te matriculaste en los siguientes cursos SAP:
@foreach($allCourses as $c)
    - {{$c['name']}}
@endforeach

@php
$sapInProgress = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'CURSANDO';
});
$aproved = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'APROBADO' || $course['certifaction_test_original'] == 'APROBADO';
});
$reproved = array_filter($allCourses, function($course) {
    return ($course['course_status_original'] == 'COMPLETA' && $course['certifaction_test_original'] == 'Reprobado') || $course['course_status_original'] == 'REPROBADO';
});
$abandoned = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'ABANDONÓ';
});
$notCompleted = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'NO CULMINÓ';
});
$toEnable = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'POR HABILITAR';
});

@endphp

Te comento sobre ellos:

@if(count($sapInProgress) > 0)
    Aún estás cursando:
    @foreach($sapInProgress as $c)
        - {{$c['name']}}
    @endforeach
@endif


@if(count($reproved) > 0)
    Completaste pero REPROBASTE:
    @foreach($reproved as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($abandoned) > 0)
    No culminaste:
    @foreach($abandoned as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($notCompleted) > 0)
    Abandonaste:
    @foreach($notCompleted as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($toEnable) > 0)
    Aún tienes por habilitar:
    @foreach($toEnable as $c)
        - {{$c['name']}}
    @endforeach

@endif

@if(count($aproved) > 0)
    Aprobaste:
    @foreach($aproved as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($toEnable) > 0)
    Aún tienes por habilitar:
    @foreach($toEnable as $c)
        - {{$c['name']}}
    @endforeach
@endif


@php

    $notCompleted = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'NO CULMINÓ';
    });
    $notCompleted = array_values($notCompleted);
    $abandoned = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'ABANDONÓ';
    });
    $abandoned = array_values($abandoned);
    $toEnable = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'POR HABILITAR';
    });
    $toEnable = array_values($toEnable);

    $inProgress = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'CURSANDO SIN CREDLY' || $course['course_status_original'] == 'CURSANDO';
    });
    $inProgress = array_values($inProgress);


    $freeCoursesWithoutExcel = array_filter($freeCourses, function($course) {
        return $course['course_id'] != 6;
    });
    $reproved = array_filter($freeCoursesWithoutExcel, function($course) {
        return ($course['course_status_original'] == 'COMPLETA SIN CREDLY' || $course['course_status_original'] == 'COMPLETA') && $course['certifaction_test_original']=="Reprobado";
    }) ?? [];
    $reproved = array_values($reproved);

    $aproved = array_filter($freeCoursesWithoutExcel, function($course) {
        return $course['course_status_original'] == 'APROBADO';
    }) ?? [];
    $aproved = array_values($aproved);

    $excel = array_filter($freeCourses, function($course) {
        return $course['course_id'] == 6;
    });
    $excel = count(array_values($excel)) > 0 ? array_values($excel)[0] : null;

    if($excel) {
        if($excel['nivel_basico']['certifaction_test_original'] == 'Aprobado') {
            $aproved[] = ['name' => 'Excel Nivel Básico'];
        } elseif ($excel['nivel_intermedio']['certifaction_test_original'] == 'Aprobado') {
            $aproved[] = ['name' => 'Excel Nivel Intermedio'];
        } elseif ($excel['nivel_avanzado']['certifaction_test_original'] == 'Aprobado') {
            $aproved[] = ['name' => 'Excel Nivel Avanzado'];
        }


        if ($excel['nivel_basico']['certifaction_test_original'] == 'Reprobado') {
            $reproved[] = ['name' => 'Excel Nivel Avanzado'];
        } elseif ($excel['nivel_intermedio']['certifaction_test_original'] == 'Reprobado') {
            $reproved[] = ['name' => 'Excel Nivel Avanzado'];
        } elseif ($excel['nivel_avanzado']['certifaction_test_original'] == 'Reprobado') {
            $reproved[] = ['name' => 'Excel Nivel Experto'];
        }

    }

@endphp

@if (count($inProgress) > 0))
    - Aún estás *cursando:* @foreach ($inProgress as $status) {{$status['name']}}, @endforeach
@endif
@if (count($reproved) > 0)
    - Completaste pero *REPROBASTE:* @foreach ($reproved as $status) {{$status['name']}}, @endforeach
@endif
@if (count($notCompleted) > 0)
    - *No culminaste:* @foreach ($notCompleted as $status) {{$status['name']}}, @endforeach
@endif
@if (count($abandoned) > 0)
    - *Abandonaste:* @foreach ($abandoned as $status) {{$status['name']}}, @endforeach
@endif
@if (count($toEnable) > 0)
    - Aún tienes *por habilitar:* @foreach ($toEnable as $status) {{$status['name']}}, @endforeach
@endif
@if (count($aproved) > 0)
    - *Aprobaste:* @foreach ($aproved as $status) {{$status['name']}}, @endforeach
@endif

Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula. Saludos.

Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y al estar abandonando:
CURSO

Aún puedes terminar y/o cursar:
CURSO
Teniendo en cuenta que si repruebas o abandonas un curso más, pierdes el acceso a:
CURSO
