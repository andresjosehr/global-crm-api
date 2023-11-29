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

        if (!$students) {
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }

        $students = array_filter($students, function ($student) {
            $freeCoursesCompleted = array_filter($student['courses'], function ($course) use ($student) {
                $now   = Carbon::now();
                $start = Carbon::parse($course['end']);
                // Get diff in days
                $diff = $start->diffInDays($now) + 1;
                return $course['type'] == 'free' && $course['course_status_original'] == 'COMPLETA' && in_array($diff, [15, 7, 4, 1]);
            });
            $freeCoursesCompleted = array_values($freeCoursesCompleted);
            return count($freeCoursesCompleted) > 0;
        });




        return $students;
    }
}
