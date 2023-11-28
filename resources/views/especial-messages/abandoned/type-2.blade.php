@php
    $sapCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'paid';
    });
    $sapCourses = array_values($sapCourse);
    $inactiveSapCourses = $student['inactive_courses'];
    $allCourses = array_merge($sapCourse, $inactiveSapCourses);
@endphp

Lamentamos no contar con tu participación en la certificación como KEY USER SAP.
@php echo "breakline"; @endphp
En este caso, te recuerdo que inicialmente te matriculaste en los siguientes cursos SAP:
@foreach($allCourses as $c)
    - {{$c['name']}}
@endforeach

@php
$sapInProgress = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'CURSANDO';
});
$aproved = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'APROBADO' || $course['certifaction_test_original'] == 'Aprobado';
});
$reproved = array_filter($allCourses, function($course) {
    return ($course['course_status_original'] == 'COMPLETA' && $course['certifaction_test_original'] == 'Reprobado') || $course['course_status_original'] == 'REPROBÓ';
});
$abandoned = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'ABANDONÓ';
});
$notCompleted = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'NO CULMINÓ';
});
$toEnable = array_filter($allCourses, function($course) {
    return $course['course_status_original'] == 'POR HABILITAR' || $course['course_status_original'] == 'PENDIENTE';
});

@endphp
@php echo "breakline"; @endphp
Te comento sobre ellos:

@if(count($sapInProgress) > 0)
    @php echo "breakline"; @endphp
    Aún estás cursando:
    @foreach($sapInProgress as $c)
        - {{$c['name']}}
    @endforeach
@endif


@if(count($reproved) > 0)
    @php echo "breakline"; @endphp
    Completaste pero REPROBASTE:
    @foreach($reproved as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($abandoned) > 0)
    @php echo "breakline"; @endphp
    Abandonaste:
    @foreach($abandoned as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($notCompleted) > 0)
    @php echo "breakline"; @endphp
    No culminaste:
    @foreach($notCompleted as $c)
        - {{$c['name']}}
    @endforeach
@endif

@if(count($toEnable) > 0)
    @php echo "breakline"; @endphp
    Aún tienes por habilitar:
    @foreach($toEnable as $c)
        - {{$c['name']}}
    @endforeach

@endif

@if(count($aproved) > 0)
    @php echo "breakline"; @endphp
    Aprobaste:
    @foreach($aproved as $c)
        - {{$c['name']}}
    @endforeach
@endif


@php

    $freeCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'free';
    });
    $freeCourses = array_values($freeCourse);

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
    $inProgressFree = array_values($inProgressFree);


    $freeCoursesWithoutExcel = array_filter($freeCourses, function($course) {
        return $course['course_id'] != 6;
    });
    $reprovedFree = array_filter($freeCoursesWithoutExcel, function($course) {
        return ($course['course_status_original'] == 'COMPLETA SIN CREDLY' || $course['course_status_original'] == 'COMPLETA') && $course['certifaction_test_original']=="Reprobado";
    }) ?? [];
    $reprovedFree = array_values($reprovedFree);

    $aprovedFree = array_filter($freeCoursesWithoutExcel, function($course) {
        return $course['certifaction_test_original'] == 'Aprobado';
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

@if(count($inProgressFree) > 0 || count($reprovedFree) > 0 || count($notCompletedFree) > 0 || count($abandonedFree) > 0 || count($toEnableFree) > 0 || count($aprovedFree) > 0)
{{-- breakline --}}
@php echo "breakline"; @endphp
Y referente a tus cursos de obsequio:
@if (count($inProgressFree) > 0)
    @php echo "breakline"; @endphp
    - Aún estás *cursando:*
    @foreach ($inProgressFree as $status)
        {{$status['name']}}
    @endforeach
@endif

@if (count($reprovedFree) > 0)
@php echo "breakline"; @endphp
    - Completaste pero *REPROBASTE:*
    @foreach ($reprovedFree as $status)
        {{$status['name']}}
    @endforeach
@endif

@if (count($notCompletedFree) > 0)
@php echo "breakline"; @endphp
    - *No culminaste:*
    @foreach ($notCompletedFree as $status)
        {{$status['name']}}
    @endforeach
@endif

@if (count($abandonedFree) > 0)
@php echo "breakline"; @endphp
    - *Abandonaste:*
    @foreach ($abandonedFree as $status)
        {{$status['name']}}
    @endforeach
@endif

@if (count($toEnableFree) > 0)
@php echo "breakline"; @endphp
    - Aún tienes *por habilitar:*
    @foreach ($toEnableFree as $status)
        {{$status['name']}}
    @endforeach
@endif

@if (count($aprovedFree) > 0)
@php echo "breakline"; @endphp
    - *Aprobaste:*
    @foreach ($aprovedFree as $status)
        {{$status['name']}}
    @endforeach
@endif
@endif


@php
    $negativeCourses = array_merge($reproved, $abandoned, $notCompleted, $reprovedFree, $abandonedFree, $notCompletedFree);
    $reprovedOrAbandoned = array_merge($reproved, $abandoned);
@endphp

@if (count($negativeCourses) > 1)
    @php echo "breakline"; @endphp
    Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y como reprobaste/abandonaste:
    @foreach ($negativeCourses as $status)
        - {{$status['name']}},
    @endforeach

    @if (count($inProgressFree) > 0)
    @php echo "breakline"; @endphp
    A pesar de haberlo iniciado, pierdes el acceso a:
    @foreach ($inProgressFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    @if (count($aprovedFree) > 0)
    @php echo "breakline"; @endphp
    Pierdes el acceso al certificado de:
    @foreach ($aprovedFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    @if (count($toEnableFree) > 0)
    @php echo "breakline"; @endphp
    Ya no podras habilitar:
    @foreach ($toEnableFree as $status)
        - {{$status['name']}},
    @endforeach
    @endif

    @php echo "breakline"; @endphp
    Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula. Saludos.

@endif



@if (count($negativeCourses) < 2)
    @php echo "breakline"; @endphp
    Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y al estar abandonando:
    @foreach ($reprovedOrAbandoned as $status)
        - {{$status['name']}},
    @endforeach


    @php
        $inProgressOrToEnableAll = array_merge($inProgressFree, $toEnableFree, $sapInProgress, $toEnable);
    @endphp

    @if (count($inProgressOrToEnableAll) > 0)
        @php echo "breakline"; @endphp
        Aún puedes terminar y/o cursar:
        @foreach ($inProgressOrToEnableAll as $status)
            - {{$status['name']}},
        @endforeach
    @endif


    @if(count($toEnableFree) > 0)
        @php echo "breakline"; @endphp
        Teniendo en cuenta que si repruebas o abandonas un curso más, pierdes el acceso a:
        @foreach ($toEnableFree as $status)
            - {{$status['name']}}
        @endforeach
    @endif

@endif
