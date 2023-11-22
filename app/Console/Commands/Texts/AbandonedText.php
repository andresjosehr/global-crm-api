<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\Processes\StudentsExcelController;
use Illuminate\Console\Command;

class AbandonedText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return any
     */
    public function handle($students = null)
    {

        if(!$students){
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }

        $studentsWithText = self::type1($students);
        // $students = self::filter($students, $studentsWithText);

        return $studentsWithText;

        return Command::SUCCESS;
    }

    // PARA EL QUE ABANDONA SAP CON CURSOS DE OBSEQUIO EN CUALQUIER ESTADO Y NO TIENE MÁS CURSOS SAP COMPRADOS
    public function type1($students){
        $students = array_filter($students, function ($student) {
            $paidCourses1 = array_filter($student['courses'], function ($course) {
                return $course['type'] == 'paid';
            });
            $paidCourses2 = array_filter($student['inactive_courses'], function ($course) {
                return $course['type'] == 'paid';
            });
            $paidCourses1 = array_values($paidCourses1);
            $paidCourses2 = array_values($paidCourses2);
            $paid_courses = array_merge($paidCourses1, $paidCourses2);

            $freeCourses = array_filter($student['courses'], function ($course) {
                return $course['type'] == 'free';
            });
            $freeCourses = array_values($freeCourses);
            return count($paid_courses) == 1 && $student['AULA SAP'] == 'ABANDONÓ' && count($freeCourses) > 0;
        });
        $students = array_values($students);
        return $students;
    }

    // PARA EL QUE ABANDONA SAP Y TIENE MÁS CURSOS SAP EN CUALQUIER ESTADO Y TIENE CURSOS OBSEQUIO EN CUALQUIER ESTADO
    public function type2($student){

    }

    // PARA EL QUE ABANDONA CURSO DE OBSEQUIO
    public function type3($student){

    }

    public function filter($students, $studentsWithText){
        // remove records in studentsWithText from students by sheet_id and course_row_number
        $students = array_filter($students, function ($student) use ($studentsWithText) {
            $studentWithText = array_filter($studentsWithText, function ($studentWithText) use ($student) {
                return $studentWithText['sheet_id'] == $student['sheet_id'] && $studentWithText['course_row_number'] == $student['course_row_number'];
            });
            return count($studentWithText) == 0;
        });
        $students = array_values($students);

        return $students;
    }
}
