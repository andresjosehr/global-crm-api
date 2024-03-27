<?php

namespace App\Console\Commands\Mails;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendUnfreezingMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-unfreezing-mails';

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
        $times = [];
        $startTime = microtime(true);
        // return ZohoToken::where('token', '<>', '')->first();
        $mode = 'prod';

        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index($mode);
        // return json_encode($students);

        $endTime = microtime(true);
        $times['ConsultaDatos'] = $endTime - $startTime;
        $startTime = $endTime;

        $students = array_map(function ($student) {
            $student['courses'] = array_map(function ($course) use ($student) {
                if ($course['type'] == 'paid') {
                    $course['access'] = $student["ACCESOS"];
                }
                if ($course['type'] == 'free') {
                    $cols = [6 => "EXC ACCESOS", 7 => "PBI ACCESOS", 8 => "PBI ACCESOS", 9 => "MSP ACCESOS"];
                    $course['access'] = $student[$cols[$course['course_id']]];
                }

                $course['_start'] = Carbon::parse($course['start'])->setTimezone('America/Lima')->startOfDay()->format('Y-m-d');
                $course['_now']   = Carbon::now()->setTimezone('America/Lima')->startOfDay()->format('Y-m-d');

                return $course;
            }, $student['courses']);
            return $student;
        }, $students);



        $students = array_filter($students, function ($student) {
            $courses = array_filter($student['courses'], function ($course) use ($student) {
                if (!$course['start'] || $course['access'] != 'CORREO CONGELAR') {
                    return null;
                }

                $now = Carbon::now()->setTimezone('America/Lima');
                $start = Carbon::parse($course['start'])->setTimezone('America/Lima')->startOfDay(); // Ajustamos al inicio del día
                $tomorrow = $now->copy()->addDay();
                // if today is saturday
                if ($now->isSaturday()) {
                    $tomorrow->addDay();
                }

                $tomorrow->addDay();
                $tomorrow->addDay();


                // Check if start is tomorrow
                if (!$start->isSameDay($tomorrow)) {
                    return false;
                }

                return true;
            });
            return count($courses) > 0;
        });


        $students = array_values($students);


        self::sendMails($students);
        self::updateExcel($students);



        return $this->line(json_encode(["Data Excel" => $students]));

        return Command::SUCCESS;
    }

    public function sendMails($students)
    {
        foreach ($students as $student) {

            $course = array_filter($student['courses'], function ($course) {
                if ($course['access'] == 'CORREO CONGELAR') {
                    return $course;
                }
            });
            $course_name = array_values($course)[0]['name'];

            $subject = "Continúa con tu Capacitación de $course_name con ¡Global Tecnologías Academy!";

            $content = view('mails.unfreezing', ['student' => $student])->render();

            $scheduleTime = Carbon::now()->setTimezone('America/Lima')->addDay()->format('m/d/Y');

            // Check if scheduleTime is sunday, if so, add one more day
            if (Carbon::parse($scheduleTime)->isSunday()) {
                $scheduleTime = Carbon::parse($scheduleTime)->addDay()->format('m/d/Y');
            }

            CoreMailsController::sendMail(
                $student['CORREO'],
                $subject,
                $content,
                $scheduleTime,
            );
        }

        return "Exito";
    }

    public function updateExcel($students)
    {

        $dataU = array_map(function ($student) {
            return [
                'value' => 'PROGRAMADO (DESCONGELAR)',
                'column' => 'K',
                'tab_id' => $student['course_tab_id'],
                'sheet_id' => $student['sheet_id'],
                'course_row_number' => $student['course_row_number']
            ];
        }, $students);


        $google_sheet = new GoogleSheetController();

        $data = $google_sheet->transformData($dataU);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return $dataU;
    }
}
