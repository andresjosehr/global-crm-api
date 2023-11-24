@php
    $sapCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'paid';
    });
    $sapCourses = array_values($sapCourse);
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

    $notCompletedFree = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'NO CULMINÓ';
    });
    $notCompletedFree = array_values($notCompletedFree);
    $abandonedFree = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'ABANDONÓ';
    });
    $abandonedFree = array_values($abandonedFree);
    $toEnableFree = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'POR HABILITAR';
    });
    $toEnableFree = array_values($toEnableFree);

    $inProgressFree = array_filter($freeCourses, function($course) {
        return $course['course_status_original'] == 'CURSANDO SIN CREDLY' || $course['course_status_original'] == 'CURSANDO';
    });
    $inProgressFree = array_values($inProgress);


    $freeCoursesWithoutExcel = array_filter($freeCourses, function($course) {
        return $course['course_id'] != 6;
    });
    $reprovedFree = array_filter($freeCoursesWithoutExcel, function($course) {
        return ($course['course_status_original'] == 'COMPLETA SIN CREDLY' || $course['course_status_original'] == 'COMPLETA') && $course['certifaction_test_original']=="Reprobado";
    }) ?? [];
    $reprovedFree = array_values($reprovedFree);

    $aprovedFree = array_filter($freeCoursesWithoutExcel, function($course) {
        return $course['course_status_original'] == 'APROBADO';
    }) ?? [];
    $aprovedFree = array_values($aprovedFree);

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

@if (count($inProgressFree) > 0)
    - Aún estás *cursando:* @foreach ($inProgressFree as $status) {{$status['name']}}, @endforeach
@endif
@if (count($reprovedFree) > 0)
    - Completaste pero *REPROBASTE:* @foreach ($reprovedFree as $status) {{$status['name']}}, @endforeach
@endif
@if (count($notCompletedFree) > 0)
    - *No culminaste:* @foreach ($notCompletedFree as $status) {{$status['name']}}, @endforeach
@endif
@if (count($abandonedFree) > 0)
    - *Abandonaste:* @foreach ($abandonedFree as $status) {{$status['name']}}, @endforeach
@endif
@if (count($toEnableFree) > 0)
    - Aún tienes *por habilitar:* @foreach ($toEnableFree as $status) {{$status['name']}}, @endforeach
@endif
@if (count($aprovedFree) > 0)
    - *Aprobaste:* @foreach ($aprovedFree as $status) {{$status['name']}}, @endforeach
@endif


@php
    $negativeCourses = array_merge($reproved, $abandoned, $notCompleted, $reprovedFree, $abandonedFree, $notCompletedFree);
    $reprovedOrAbandoned = array_merge($reproved, $abandoned);
@endphp

@if (count($negativeCourses) > 0 && count($reprovedOrAbandoned) > 1)
    Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y como reprobaste/abandonaste:
    @foreach ($negativeCourses as $status)
        - {{$status['name']}},
    @endforeach

    @if (count($inProgressFree) > 0)
    A pesar de haberlo iniciado, pierdes el acceso a:
    @foreach ($inProgressFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    @if (count($aprovedFree) > 0)
    Pierdes el acceso al certificado de:
    @foreach ($aprovedFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    @if (count($toEnableFree) > 0)
    Pierdes el acceso al certificado de:
    @foreach ($toEnableFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula. Saludos.

@endif



@if (count($reprovedOrAbandoned) == 1)
    Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y al estar abandonando:
    @foreach ($reprovedOrAbandoned as $status)
        - {{$status['name']}},
    @endforeach

    @if (count($inProgressFree) > 0)
    A pesar de haberlo iniciado, pierdes el acceso a:

@endif


Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y al estar abandonando:
CURSO
@php
    $inProgressOrToEnableFree = array_merge($inProgressFree, $toEnableFree);
@endphp

@if (count($inProgressOrToEnableFree) > 0)
    Aún puedes terminar y/o cursar:
    @foreach ($inProgressOrToEnableFree as $status)
        - {{$status['name']}},
    @endforeach
@endif


@if(count($toEnableFree) > 0)
    Pierdes el acceso al certificado de:
    @foreach ($toEnableFree as $status)
        - {{$status['name']}},
    @endforeach
@endif
