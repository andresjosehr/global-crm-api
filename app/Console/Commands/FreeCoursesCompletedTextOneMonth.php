<?php

namespace App\Console\Commands;

use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeCoursesCompletedTextOneMonth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-complete-free-courses-onemonth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle($students = null)
    {
        if (!$students) {
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }
        // Filtrar estudiantes que tienen cursos de tipo "free", course_status_original COMPLETA  o COMPLETA SIN CREDLY y
        //certifaction_test_original 'Sin Intentos Gratis'
        $students = array_filter($students, function ($student) {
            $freeAndCompletedCourses = self::filtreFeeAndCompleteCourses($student);
            return !empty($freeAndCompletedCourses);
        });

        $students = array_values($students);


        // transformar student para agregarle diff_days en course
        $students = array_map(function ($student) {
            $student['NOMBRE'] = (isset($student['NOMBRE'])) ? $student['NOMBRE'] : $student['NOMBRES Y APELLIDOS'];

            $courses = array_map(function ($course) use ($student) {
                $now   = Carbon::now();
                $end = Carbon::parse($course['end']);
                $month_days = $end->daysInMonth;
                $diff = $end->diffInDays($now) + 1;
                $course['diff_days'] = $diff;
                $course['month_days'] = $month_days;

                return $course;
            }, $student['courses']);
            $student['courses'] = $courses;

            $student['text'] = view('especial-messages.complete-free-courses.1-mes', ['student' => $student])->render();
            $student['text'] = preg_replace("/^\s+/m", "", $student['text']);
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            $student['text'] = str_replace('breakline', "\n", $student['text']);


            return $student;
        }, $students);
        dd($students);
        return $this->line(json_encode($students));
    }

    public static function filtreFeeAndCompleteCourses($student)
    {
        $freeAndCompletedCourses = array_filter($student['courses'], function ($course) {
            $isExcelEmpresarial = $course['name'] == 'Excel Empresarial';

            if ($isExcelEmpresarial) {
                return (
                    $course['type'] == 'free' &&
                    in_array($course['course_status_original'], ['COMPLETA', 'COMPLETA SIN CREDLY']) &&
                    $course['end'] != null &&
                    (
                        $course['nivel_basico']['certifaction_test_original'] == 'Sin Intentos Gratis' ||
                        $course['nivel_intermedio']['certifaction_test_original'] == 'Sin Intentos Gratis' ||
                        $course['nivel_avanzado']['certifaction_test_original'] == 'Sin Intentos Gratis'
                    )
                );
            }
            // No es 'Excel Empresarial', aplicar otras condiciones si es necesario
            return (
                $course['type'] == 'free' &&
                in_array($course['course_status_original'], ['COMPLETA', 'COMPLETA SIN CREDLY']) &&
                isset($course['certifaction_test_original']) &&
                $course['certifaction_test_original'] == 'Sin Intentos Gratis' &&
                $course['end'] != null
            );
        });

        return $freeAndCompletedCourses;
    }
}
