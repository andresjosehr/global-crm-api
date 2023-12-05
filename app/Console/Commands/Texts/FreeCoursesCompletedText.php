<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeCoursesCompletedText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-complete-free-courses-text';

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

        if (!$students) {
            $data = new StudentsExcelController();
            $students = $data->index('prod');
        }

        $students = array_filter($students, function ($student) {
            $freeCoursesCompleted = array_filter($student['courses'], function ($course) use ($student) {
                $now   = Carbon::now();
                $start = Carbon::parse($course['end']);
                // Get diff in days
                $diff = $start->diffInDays($now) + 1;
                return $course['type'] == 'free' && $course['course_status_original'] == 'COMPLETA' && in_array($diff, [15, 7, 4, 1]) && $course['end'] != null;
            });
            $freeCoursesCompleted = array_values($freeCoursesCompleted);
            return count($freeCoursesCompleted) > 0;
        });

        $students = array_values($students);


        $students = array_map(function ($student) {
            $courses = array_map(function ($course) use ($student) {
                $now   = Carbon::now();
                $start = Carbon::parse($course['end']);
                // Get diff in days
                $diff = $start->diffInDays($now) + 1;
                $course['diff_days'] = $diff;
                return $course;
            }, $student['courses']);
            $student['courses'] = $courses;


            $student['text'] = view('especial-messages.complete-free-courses.1-dia', ['student' => $student])->render();
            $student['text'] = preg_replace("/^\s+/m", "", $student['text']);
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            // Replace all "breakline" text to \n
            $student['text'] = str_replace('breakline', "\n", $student['text']);


            return $student;
        }, $students);




        return $this->line(json_encode($students));
    }
}
