Lamentamos no contar con tu participación en esta certificación.

@php

    $mixed1 = array_filter($student['courses'], function($course) {
        return ($course['type'] === 'free' && $course['course_status_original'] === 'CURSANDO') ||
                ($course['type'] === 'paid' && $course['course_status_original'] === 'CURSANDO' || $course['course_status_original'] === 'COMPLETA');
    });
    $mixed1 = array_values($mixed1);


    $mixed2 = array_filter($student['courses'], function($course) {
        return $course['course_id'] != 6;
    });
    $mixed2 = array_filter($mixed2, function($course) {
        return ($course['type'] === 'free' && $course['certifaction_test_original'] === 'Reprobado');
    });
    $mixed2= array_values($mixed2);
    $mixed2 = array_merge($mixed2, array_values(array_filter($student['inactive_courses'], function($course) {
        return ($course['type'] === 'paid' && $course['course_status_original'] === 'Reprobado');
    })));



    $mixed3 = array_filter($student['courses'], function($course) {
        return ($course['type'] === 'free' && $course['course_status_original'] === 'NO CULMINÓ');
    });
    $mixed3 = array_values($mixed3);
    $mixed3 = array_merge($mixed3, array_values(array_filter($student['inactive_courses'], function($course) {
        return ($course['type'] === 'paid' && $course['course_status_original'] === 'NO CULMINÓ');
    })));



    $mixed4 = array_filter($student['courses'], function($course) {
        return ($course['type'] === 'free' && $course['course_status_original'] === 'ABANDONÓ');
    });
    $mixed4 = array_values($mixed4);
    $mixed4 = array_merge($mixed4, array_values(array_filter($student['inactive_courses'], function($course) {
        return ($course['type'] === 'paid' && $course['course_status_original'] === 'ABANDONÓ');
    })));

    $mixed5 = array_filter($student['courses'], function($course) {
        return ($course['type'] === 'free' && $course['course_status_original'] === 'POR HABILITAR');
    });
    $mixed5 = array_values($mixed5);
    $mixed5 = array_merge($mixed5, array_values(array_filter($student['inactive_courses'], function($course) {
        return ($course['type'] === 'paid' && $course['course_status_original'] === 'PENDIENTE');
    })));

    $mixed6 = array_filter($student['courses'], function($course) {
        return $course['course_id'] != 6;
    });
    $mixed6 = array_values($mixed6);
    $mixed6 = array_filter($mixed6, function($course) {
        return ($course['type'] === 'free' && $course['certifaction_test_original'] === 'Aprobado');
    });
    $mixed6 = array_values($mixed6);
    $mixed6 = array_merge($mixed6, array_values(array_filter($student['inactive_courses'], function($course) {
        return ($course['type'] === 'paid' && $course['course_status_original'] === 'CERTIFICADO');
    })));



    $freeCourses = array_filter($student['courses'], function($course) {
        return $course['type'] === 'free';
    });

    $excel = array_filter($freeCourses, function($course) {
        return $course['course_id'] == 6;
    });
    $excel = count(array_values($excel)) > 0 ? array_values($excel)[0] : null;

    $excelAproved = [];
    $excelReproved = [];
    if($excel) {
        if($excel['nivel_basico']['certifaction_test_original'] == 'Aprobado') {
            $excelAproved[] = ['name' => 'Excel Nivel Básico'];
        }
        if ($excel['nivel_intermedio']['certifaction_test_original'] == 'Aprobado') {
            $excelAproved[] = ['name' => 'Excel Nivel Intermedio'];
        }
        if ($excel['nivel_avanzado']['certifaction_test_original'] == 'Aprobado') {
            $excelAproved[] = ['name' => 'Excel Nivel Avanzado'];
        }


        if ($excel['nivel_basico']['certifaction_test_original'] == 'Reprobado') {
            $excelReproved[] = ['name' => 'Excel Nivel Avanzado'];
        }
        if ($excel['nivel_intermedio']['certifaction_test_original'] == 'Reprobado') {
            $excelReproved[] = ['name' => 'Excel Nivel Avanzado'];
        }
        if ($excel['nivel_avanzado']['certifaction_test_original'] == 'Reprobado') {
            $excelReproved[] = ['name' => 'Excel Nivel Experto'];
        }

        if(count($excelReproved) > 0) {
            $excel['certifaction_test_original'] = 'Reprobado';
            $mixed2[] = $excel;
        }

    }

@endphp
@php echo "breakline"; @endphp
En esta caso, te haré un resumen sobre los cursos adquiridos:

@if(count($mixed1) > 0)
@php echo "breakline"; @endphp
Estás *cursando:*
@foreach($mixed1 as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($mixed2) > 0 || count($excelReproved) > 0)
@php echo "breakline"; @endphp
*REPROBASTE:*
@foreach($mixed2 as $c)
    - {{$c['name']}}
@endforeach
@foreach($excelReproved as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($mixed3) > 0)
@php echo "breakline"; @endphp
*No culminaste:*
@foreach($mixed3 as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($mixed4) > 0)
@php echo "breakline"; @endphp
*Abandonaste:*
@foreach($mixed4 as $c)
    - {{$c['name']}}
@endforeach

@if(count($mixed5) > 0)
@php echo "breakline"; @endphp
Aún tienes *por habilitar:*
@foreach($mixed5 as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($mixed6) > 0 || count($excelAproved) > 0)
@php echo "breakline"; @endphp
*Aprobaste:*
@foreach($mixed6 as $c)
    - {{$c['name']}}
@endforeach
@foreach($excelAproved as $c)
    - {{$c['name']}}
@endforeach
@endif

@php
    $mixed7 = array_merge($mixed2, $mixed3, $mixed4);
@endphp



@php
    $freeCoursesInProgress = array_filter($student['courses'], function($course) {
        return $course['type'] === 'free' && ($course['course_status_original'] === 'CURSANDO' || $course['course_status_original'] === 'CURSANDO SIN CREDLY' || $course['course_status_original'] === 'CURSANDO AVANZADO' || $course['course_status_original'] === 'CURSANDO AVANZADO SIN CREDLY');
    });
    $freeCoursesInProgress = array_values($freeCoursesInProgress);

    $freeCoursesAproved = array_filter($student['courses'], function($course) {
        return $course['course_id'] !=6;
    });
    $freeCoursesAproved = array_values($freeCoursesAproved);

    $freeCoursesAproved = array_filter($freeCoursesAproved, function($course) {
        return $course['type'] === 'free' && ($course['certifaction_test_original'] === 'Aprobado' || $course['certificate'] === 'EMITIDO');
    });

    $freeCoursesAproved = array_values($freeCoursesAproved);

    $freeCoursesToEnable = array_filter($student['courses'], function($course) {
        return $course['type'] === 'free' && $course['course_status_original'] === 'POR HABILITAR';
    });
    $freeCoursesToEnable = array_values($freeCoursesToEnable);

@endphp

@if(count($mixed7) > 1)
@php echo "breakline"; @endphp
Recuerda que como condición, no puedes reprobar/abandonar dos cursos o más. Y en este caso estás cumpliendo esta condición con los cursos:
@foreach($mixed7 as $c)
    - {{$c['name']}}
@endforeach


@php echo "breakline"; @endphp

Por lo que:
@if(count($freeCoursesInProgress) > 0)
@php echo "breakline"; @endphp
A pesar de haberlo iniciado, pierdes el acceso a:
@foreach($freeCoursesInProgress as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($freeCoursesAproved) > 0)
@php echo "breakline"; @endphp
Pierdes el acceso al certificado de:
@foreach($freeCoursesAproved as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($freeCoursesToEnable) > 0)
@php echo "breakline"; @endphp
Y ya no podrás habilitar:
@foreach($freeCoursesToEnable as $c)
    - {{$c['name']}}
@endforeach
@endif

@php echo "breakline"; @endphp
Al no tener más cursos pendientes con nosotros, con esto cerramos formalmente tu matrícula. Saludos.
@endif


@if(count($mixed7) == 1)
@php echo "breakline"; @endphp
Recuerda que como condición, no puedes tener dos o más cursos reprobados/abandonados, y al estar abandonando:
@foreach($mixed7 as $c)
    - {{$c['name']}}
@endforeach



@php
    $sapCourses = array_filter($student['courses'], function($course) {
        return $course['type'] === 'paid' && ($course['course_status_original'] === 'CURSANDO' || $course['course_status_original'] === 'COMPLETA');
    });
    $mixed8 = array_merge($sapCourses, $freeCoursesInProgress, $freeCoursesToEnable);
@endphp

@if(count($mixed8) > 0)
@php echo "breakline"; @endphp
Aún puedes terminar y/o cursar:
@foreach($mixed8 as $c)
    - {{$c['name']}}
@endforeach
@endif

@if(count($freeCoursesToEnable) > 0)
@php echo "breakline"; @endphp
Teniendo en cuenta que si repruebas o abandonas un curso más, pierdes el acceso a:
@foreach($freeCoursesToEnable as $c)
    - {{$c['name']}}
@endforeach
@endif

@endif

@endif

