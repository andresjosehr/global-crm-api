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
     * @return int
     */
    public function handle($students = null)
    {

        if(!$students){
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }

        $students = array_filter($students, function ($student) {
            $paidCourses1 = array_filter($student['courses'], function ($course) {
                return $course['type'] == 'paid';
            });
            $paidCourses2 = array_filter($student['inactive_courses'], function ($course) {
                return $course['type'] == 'paid';
            });
            $paid_courses = array_merge($paidCourses1, $paidCourses2);
            return count($paid_courses) == 1 && $student['AULA SAP'] == 'ABANDONÓ';
        });
        $students = array_values($students);
        return $students;


        return Command::SUCCESS;
    }
}
