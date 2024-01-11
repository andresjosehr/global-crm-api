{{--

SUBPLANTILLA PARA INFORMAR LOS AVANCES DE CADA NIVEL DE LOS CURSOS

"(NOMBRE DEL CURSO o NIVEL DE EXCEL), tiene (CANTIDAD DE LECCIONES COMPLETAS EN EL AULA) lecciones completas, y en total son (TOTAL DE LECCIONES DEPENDIENDO DEL CURSO)."

--}}
@foreach ($coursesToNotify as $course)
        @if ($course['isExcelCourse'] == false)
{{$course['name']}}, tiene {{$course['lessons_completed']}} lecciones completas, y en total son {{$course['lessons_count']}}.
        @else
                @foreach($course['LEVELS'] as $level)
{{$course['name']}} {{$course[$level]['name']}}, tiene {{$course[$level]['lessons_completed']}} lecciones completas, y en total son {{$course[$level]['lessons_count']}}.
                @endforeach
        @endif
@endforeach