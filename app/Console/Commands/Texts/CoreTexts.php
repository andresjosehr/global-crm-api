<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Illuminate\Console\Command;

class CoreTexts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-texts';

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
        $data = new StudentsExcelController();
        $students = $data->index('prod');

        $students = array_map(function ($student) {
            $student['include_text'] = false;
            return $student;
        }, $students);

        $unfreezingTexts = new UnfreezingText();
        $studentsWithText = $unfreezingTexts->handle($students);
        $students = self::filter($students, $studentsWithText);

        $abandonedTexts = new AbandonedText();
        $studentsWithText = $abandonedTexts->handle($students);
        $students = self::filter($students, $studentsWithText);


        // return $this->line(json_encode($studentsWithText));

        $dataToUpdate = [];

        foreach ($studentsWithText as $student) {
                $dataToUpdate[] = [
                    'sheet_id'          => $student['sheet_id'],
                    'course_row_number' => $student['course_row_number'],
                    'column'            => "BB",
                    'email'             => $student['CORREO'],
                    'tab_id'            => $student['course_tab_id'],
                    'value'             => $student['text'],
                ];
        }
        foreach($students as $student){
            $dataToUpdate[] = [
                'sheet_id'          => $student['sheet_id'],
                'course_row_number' => $student['course_row_number'],
                'column'            => "BB",
                'email'             => $student['CORREO'],
                'tab_id'            => $student['course_tab_id'],
                'value'             => '',
            ];
        }

        $google_sheet = new GoogleSheetController();
        $data = $google_sheet->transformData($dataToUpdate);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return $this->line(json_encode($dataToUpdate));
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
