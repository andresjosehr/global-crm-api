<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Processes\StudentsExcelController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AbandonedText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-abandoned';

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

        $studentsWithText = self::type1($students);
        $students = self::filter($students, $studentsWithText);

        $studentsWithText = array_merge($studentsWithText, self::type2($students));
        $students = self::filter($students, $studentsWithText);


        $studentsWithText = array_merge($studentsWithText, self::type3($students));
        $students = self::filter($students, $studentsWithText);

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

        return $this->line(json_encode($studentsWithText));

    }

    // PARA EL QUE ABANDONA SAP CON CURSOS DE OBSEQUIO EN CUALQUIER ESTADO Y NO TIENE MÁS CURSOS SAP COMPRADOS
    public function type1($students)
    {
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

        $students = array_map(function ($student) {
            $student['include_text'] = 'type-1';
            $student['text'] = view('especial-messages.abandoned.type-1', ['student' => $student])->render();
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            return $student;
        }, $students);


        return $students;
    }

    // PARA EL QUE ABANDONA SAP Y TIENE MÁS CURSOS SAP EN CUALQUIER ESTADO Y TIENE CURSOS OBSEQUIO EN CUALQUIER ESTADO
    public function type2($students)
    {
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
            return count($paid_courses) > 1 && $student['AULA SAP'] == 'ABANDONÓ' && count($freeCourses) > 0;
        });

        $students = array_values($students);


        $courses_ids = DB::table('courses')->select('id', 'short_name')->get()->pluck('id', 'short_name')->toArray();
        $students = array_map(function ($student) use ($courses_ids) {
            foreach(['ESTADO', 'OBSERVACIONES'] as $column){
                foreach(['REPROBÓ', 'NO CULMINÓ', 'ABANDONÓ', 'PENDIENTE', 'CERTIFICADO'] as $status){
                    if (strpos($student[$column], $status) !== false) {
                        $courses = explode($status, $student[$column])[1];
                        $courses = explode('/', $courses)[0];
                        $courses = trim($courses);
                        $courses = explode(' ', $courses);

                        $student['courses_'.$status] = [];

                        foreach ($courses as $course) {
                            // If not include SAP
                            if(!strpos($course, 'SAP')){
                                $course = 'SAP ' . $course;
                            }

                            //
                            if (isset($courses_ids[$course])) {
                                $index = array_search($courses_ids[$course], array_column($student['inactive_courses'], 'course_id'));
                                if ($index !== false) {
                                    $student['inactive_courses'][$index]['course_status_original'] = $status;
                                }
                            }
                        }
                    }
                }
            }

            $student['include_text'] = 'type-2';
            $student['text'] = view('especial-messages.abandoned.type-2', ['student' => $student])->render();
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            return $student;
        }, $students);



        return $students;
    }

    // PARA EL QUE ABANDONA CURSO DE OBSEQUIO
    public function type3($students)
    {
        $students = array_filter($students, function ($student) {
            $freeCourses = array_filter($student['courses'], function ($course) {
                return $course['type'] == 'free' && $course['course_status_original'] == 'ABANDONÓ';
            });
            return count($freeCourses) > 0;
        });

        $students = array_values($students);

        $students = array_map(function($student){
            foreach(['ESTADO', 'OBSERVACIONES'] as $column){
                foreach(['REPROBÓ', 'NO CULMINÓ', 'ABANDONÓ', 'PENDIENTE', 'CERTIFICADO'] as $status){
                    if (strpos($student[$column], $status) !== false) {
                        $courses = explode($status, $student[$column])[1];
                        $courses = explode('/', $courses)[0];
                        $courses = trim($courses);
                        $courses = explode(' ', $courses);

                        $student['courses_'.$status] = [];

                        foreach ($courses as $course) {
                            // If not include SAP
                            if(!strpos($course, 'SAP')){
                                $course = 'SAP ' . $course;
                            }

                            //
                            if (isset($courses_ids[$course])) {
                                $index = array_search($courses_ids[$course], array_column($student['inactive_courses'], 'course_id'));
                                if ($index !== false) {
                                    $student['inactive_courses'][$index]['course_status_original'] = $status;
                                }
                            }
                        }
                    }
                }
            }
            $student['include_text'] = 'type-3';
            $student['text'] = view('especial-messages.abandoned.type-3', ['student' => $student])->render();
            $student['text'] = preg_replace("/[\r\n]+/", "\n", $student['text']);
            return $student;
        }, $students);

        return $students;
    }

    public function filter($students, $studentsWithText)
    {
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
