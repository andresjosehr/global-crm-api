@php
    $sapCourse = array_filter($student['courses'], function($course) {
        return $course['type'] == 'paid';
    });
    $sapCourse = array_values($sapCourse)[0];
@endphp

Lamentamos no contar con tu participación en la certificación como KEY USER SAP para el curso {{$sapCourse['name']}}.
@php echo "breakline"; @endphp
En este caso, como *estás abandonando el curso principal ({{$sapCourse['name']}}),* te comento lo siguiente sobre tus cursos de obsequio:

{{-- Foreach --}}
@php
    $freeCourses = array_filter($student['courses'], function($course) {
        return $course['type'] == 'free';
    });
    $freeCourses = array_values($freeCourses);
@endphp


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
        return $course['certifaction_test_original'] == 'Aprobado';
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
    @php echo "breakline"; @endphp
    - Aún estás *cursando:*
    @foreach ($inProgress as $status)
        {{$status['name']}}
    @endforeach
@endif
@if (count($reproved) > 0)
    @php echo "breakline"; @endphp
    - Completaste pero *REPROBASTE:*
    @foreach ($reproved as $status)
        {{$status['name']}}
    @endforeach
@endif
@if (count($notCompleted) > 0)
    - *No culminaste:*
    @foreach ($notCompleted as $status)
    {{$status['name']}}
    @endforeach
@endif
@if (count($abandoned) > 0)
    @php echo "breakline"; @endphp
    - *Abandonaste:*
    @foreach ($abandoned as $status)
        {{$status['name']}}
    @endforeach
@endif
@if (count($toEnable) > 0)
    @php echo "breakline"; @endphp
    - Aún tienes *por habilitar:*
    @foreach ($toEnable as $status)
        {{$status['name']}}
    @endforeach
@endif
@if (count($aproved) > 0)
    @php echo "breakline"; @endphp
    - *Aprobaste:*
    @foreach ($aproved as $status)
        {{$status['name']}}
    @endforeach
@endif

@php echo "breakline"; @endphp

Por lo que, al abandonar el curso principal, que es SAP:

@if(count($inProgress) > 0)
@php echo "breakline"; @endphp
Automáticamente pierdes el acceso a este curso, pesar de haberlo iniciado:
    @foreach($inProgress as $status)
        - {{$status['name']}}
    @endforeach
@endif

@if (count($aproved) > 0)
@php echo "breakline"; @endphp
Pierdes el acceso al certificado de:
    @foreach($aproved as $status)
        - {{$status['name']}}
    @endforeach
@endif

@if (count($toEnable) > 0)
@php echo "breakline"; @endphp
Y ya no podrás habilitar:
    @foreach($toEnable as $status)
        - {{$status['name']}}
    @endforeach
@endif
@php echo "breakline"; @endphp

Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula.

Saludos.
