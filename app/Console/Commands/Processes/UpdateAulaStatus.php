<?php

namespace App\Console\Commands\Processes;

use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Course;
use App\Models\Wordpress\WpLearnpressUserItem;
use App\Models\Wordpress\WpPost;
use App\Models\Wordpress\WpPostMeta;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAulaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-aula-status';

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
        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index('prod');

        $students = array_filter($students, function ($student) {
            return $student['wp_user_id'] ? true : false;
        });

        $students = array_values($students);

        $courses_ids = Course::select('id', 'wp_post_id')->get()->pluck('wp_post_id', 'id')->toArray();
        $students = array_map(function ($student) use ($courses_ids) {
            $courses = array_map(function ($course) use ($student, $courses_ids) {
                $order                = WpLearnpressUserItem::select('ref_id')->where('user_id', $student['wp_user_id'])->where('item_id', $courses_ids[$course['course_id']])->first();
                $course['order_id']   = $order ? $order->ref_id : null;
                $course['wp_post_id'] = $courses_ids[$course['course_id']];



                if($course['access']==='CORREO CONGELAR' || $course['access']==='ABANDONÓ' || $course['end']==null){
                    $course['end'] = '2021-01-01';
                    $course['status'] = 'Inactivo';

                    return $course;
                }

                // Check if $course['end'] is less than today
                $now   = Carbon::now()->setTimezone('America/Lima');
                $start = Carbon::parse($course['start'])->setTimezone('America/Lima');
                $end   = Carbon::parse($course['end'] . ' 23:59:59')->setTimezone('America/Lima')->setTime(23, 59, 59);

                if ($now->greaterThan($end)) {
                    $course['status'] = 'Inactivo';
                }
                if($now->greaterThan($start) && $now->lessThan($end)){
                    $course['status'] = 'Activo';
                }

                if ($now->lessThan($start)) {
                    $course['status'] = 'Inactivo';
                }

                return $course;

            }, $student['courses']);


            $courses = array_filter($courses, function ($course){
                return $course['order_id'] ? true : false;
            });
            $courses = array_values($courses);



            $inactive_courses = array_map(function ($course) use ($student, $courses_ids) {
                $course['end'] = '2021/01/01';
                $course['status'] = 'Inactivo';
                $order = WpLearnpressUserItem::select('ref_id')->where('user_id', $student['wp_user_id'])->where('item_id', $courses_ids[$course['course_id']])->first();
                $course['order_id'] = $order ? $order->ref_id : null;
                return $course;
            }, $student['inactive_courses']);

            $inactive_courses = array_filter($inactive_courses, function ($course) use ($student, $courses_ids) {
                return $course['order_id'] ? true : false;
            });
            $inactive_courses = array_values($inactive_courses);

            $student['courses'] = $courses;

            $student['inactive_courses'] = $inactive_courses;
            return $student;
        }, $students);


        $dataToUpdate = [];
        array_map(function ($student) use (&$dataToUpdate) {
            array_map(function ($course) use ($student, &$dataToUpdate) {
                $dataToUpdate[] = [
                    'email'    => $student['CORREO'],
                    'course'   => $course['name'],
                    'order_id' => $course['order_id'],
                    'status'   => $course['status'],
                    'end'      => $course['end'],
                ];
            }, $student['courses']);
            array_map(function ($course) use ($student, &$dataToUpdate) {
                $dataToUpdate[] = [
                    'email'    => $student['CORREO'],
                    'course'   => $course['name'],
                    'order_id' => $course['order_id'],
                    'status'   => $course['status'],
                    'end'      => $course['end'],
                ];
            }, $student['inactive_courses']);
        }, $students);

        // return json_encode(["Exito" =>$dataToUpdate]);

        foreach ($dataToUpdate as $record) {
            WpPostMeta::updateOrCreate(
                [
                    'post_id' => $record['order_id'],
                    'meta_key' => 'lpa_lesson_status'
                ],
                ['meta_value' => $record['status']]
            );


            if($meta = WpPost::with('meta')->where('ID', $record['order_id'])->first()->meta->where('meta_key', 'fecha_expiracion')->first()){
                $meta->meta_value = $record['end'];
                $meta->save();
              } else {
                WpPost::with('meta')->where('ID', $record['order_id'])->first()->meta()->create([
                  'meta_key' => 'fecha_expiracion',
                  'meta_value' => $record['end']
                ]);
              }
        }

        return $this->line(json_encode(["Exito" => $students]));
    }
}

