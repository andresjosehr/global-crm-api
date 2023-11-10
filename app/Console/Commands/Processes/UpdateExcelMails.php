<?php

namespace App\Console\Commands\Processes;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Illuminate\Console\Command;

class UpdateExcelMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-excel-mails';

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
    public function handle()
    {
        $mode = 'test';

        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index($mode);

        $studentsFiltered = array_filter($students, function($student){
            $courses = array_filter($student['courses'], function($course){
                return $course['access'] == 'PROGRAMADO' || $course['access'] == 'PROGRAMADO (DESCONGELAR)';
            });
            $courses = array_values($courses);
            return count($courses) > 0;
        });

        $studentsFiltered = array_map(function($student){
            $courses = array_filter($student['courses'], function($course){
                return $course['access'] == 'PROGRAMADO' || $course['access'] == 'PROGRAMADO (DESCONGELAR)';
            });

            $student['courses'] = array_values($courses);
            return $student;
        }, $studentsFiltered);

        $studentsFiltered = array_values($studentsFiltered);
        // return json_encode($studentsFiltered);


        $data = [];
        array_map(function($student) use (&$data){
            array_map(function($course) use (&$data, $student){

                $cols = [6 => 'U', 7 => 'AI', 8 => 'AI', 9 => 'AQ'];
                $data[] = [
                'column'            => $course['type']=='paid' ? 'K' : $cols[$course['course_id']],
                'value'             => 'ENVIADOS',
                'tab_id'            => $student['course_tab_id'],
                'course_row_number' => $student['course_row_number'],
                'sheet_id'          => $student['sheet_id'],
                ];

            }, $student['courses']);
        }, $studentsFiltered);


        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($data);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        print_r(["Exito" => $studentsFiltered]);
        return Command::SUCCESS;
    }
}
