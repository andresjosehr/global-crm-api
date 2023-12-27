<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UnfreezingText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-unfreezing-texts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     *
     */
    public function handle($students = null)
    {

        if(!$students){
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }

        $studentsFitered = array_map(function ($student) {
            $student['include_text'] = $student['ACCESOS'] == 'CORREO CONGELAR';
            return $student;
        }, $students);

        $studentsFitered =  array_values($studentsFitered);

        $courses_ids = DB::table('courses')->select('id', 'short_name')->get()->pluck('id', 'short_name')->toArray();

        $studentsFitered = array_map(function ($student) use ($courses_ids){
            $student['courses'] = array_filter($student['courses'], function ($course) {
                return $course['type'] == 'paid';
            });
            $student['courses'] = array_values($student['courses']);

            if (strpos($student['ESTADO'], 'DESCONGELADO') !== false) {
                $courses = explode('DESCONGELADO', $student['ESTADO'])[1];
                $courses = explode('/', $courses)[0];
                $courses = trim($courses);
                $courses = explode(' ', $courses);

                $student['courses_DESCONGELADO'] = [];

                foreach ($courses as $course) {
                    // If not include SAP
                    if(!strpos($course, 'SAP')){
                        $course = 'SAP ' . $course;
                    }

                    //
                    if (isset($courses_ids[$course])) {
                        $index = array_search($courses_ids[$course], array_column($student['courses'], 'course_id'));
                        if ($index !== false) {
                            $student['courses'] = [];
                        }
                    }
                }
            }


            return $student;
        }, $studentsFitered);

        $studentsFitered = array_map(function ($student) {
            if (count($student['courses']) == 0 || $student['include_text'] == false) {
                $student['include'] = '';
                $student['include_text'] = false;
                return $student;
            }
            $now   = Carbon::now();
            $start = Carbon::parse($student['courses'][0]['start']);
            // Get diff in days
            $diff = $start->diffInDays($now) + 1;

            // switch case
            switch ($diff) {
                case 15:
                    $student['template'] = '15-dias';
                    break;
                case 7:
                    $student['template'] = '7-dias';
                    break;
                case 4:
                    $student['template'] = '4-dias';
                    break;
                case 1:
                    $student['template'] = '2-dias';
                    break;
                default:
                    $student['template'] = '';
                    $student['include_text'] = false;
                break;
            }

            if($student['include_text']){
                $s = [
                    'student_name' => isset($student['NOMBRE']) ? $student['NOMBRE'] : '',
                    'courses' => $student['courses'],
                ];
                $text = view('especial-messages.unfreezing-texts.' . $student['template'], $s)->render();
                // remove \n consecutives. Only two \n
                $text = preg_replace("/\n\n+/", "\n\n", $text);

                $student['text'] = $text;
            }
            return $student;
        }, $studentsFitered);



        $studentsFitered = array_filter($studentsFitered, function ($student) {
            return $student['include_text'] == 'unfreezing';
        });

        $studentsFitered = array_values($studentsFitered);

        return $studentsFitered;

        $dataToUpdate = [];

        foreach ($studentsFitered as $student) {
            foreach ($student['courses'] as $course) {
                $dataToUpdate[] = [
                    'sheet_id'          => $student['sheet_id'],
                    'course_row_number' => $student['course_row_number'],
                    'column'            => "BA",
                    'email'             => $student['CORREO'],
                    'tab_id'            => $student['course_tab_id'],
                    'value'             => $student['text'],
                ];
            }
        }



        $google_sheet = new GoogleSheetController();
        $data = $google_sheet->transformData($dataToUpdate);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return $this->line(json_encode($dataToUpdate));

    }
}
