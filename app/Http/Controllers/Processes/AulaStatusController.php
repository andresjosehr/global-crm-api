<?php

namespace App\Http\Controllers\Processes;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Wordpress\WpLearnpressUserItem;
use Illuminate\Http\Request;

class AulaStatusController extends Controller
{
    public function index()
    {
        // Memory limit
        ini_set('memory_limit', -1);

        $data = new StudentsExcelController();
        $students = $data->index('prod');

        $students = array_filter($students, function ($student) {
            if (!$student['wp_user_id']) {
                return $student;
            }
        });

        $courses_ids = Course::select('id', 'wp_post_id')->get()->pluck('wp_post_id', 'id')->toArray();
        $students = array_map(function ($student) use ($courses_ids) {
            $courses = array_map(function ($course) use ($student, $courses_ids) {
                $course['order_id'] = WpLearnpressUserItem::select('ref_id')->where('user_id', $student['wp_user_id'])->where('item_id', $courses_ids[$course['course_id']])->first()->ref_id;
            }, $student['courses']);
            $student['courses'] = $courses;
            return $student;
        }, $students);

        return json_encode($students);
    }
}
