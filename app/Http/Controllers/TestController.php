<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\DatesHistory;
use App\Models\OderDateHistory;
use App\Models\Order;
use App\Models\OrderCourse;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $order_course_id = 1;

        $order_course  = OrderCourse::where('id', $order_course_id)->with('dateHistory', 'course', 'order.student', 'freezings')->first();
        $student       = $order_course->order->student;
        $dates_history = $order_course->dateHistory;
        $course        = $order_course->course;
        $freezing      = $order_course->freezings->last();
        $remainFreezingDurationAvaliable = 3 - $order_course->freezings->reduce(function ($carry, $item) {
            $times = ['1 Mes' => 1, '2 Meses' => 2, '3 Meses' => 3];
            return $carry + $times[$item->duration];
        }, 0);

        // Get date history record before the freezing by id
        $dateRecord = DatesHistory::where('order_course_id', $order_course_id)->get();
        // Get index of the date history record before the freezing
        $index = $dateRecord->search(function ($item) use ($freezing) {
            return $item->freezing_id == $freezing->id;
        })-1;
        $original_date = $dateRecord[$index];

        return view('mails.freezing', [
            'order_course'  => $order_course,
            'student'       => $student,
            'dates_history' => $dates_history,
            'original_date' => $original_date,
            'course'        => $course,
            'freezing'      => $freezing,
            'remainFreezingDurationAvaliable' => $remainFreezingDurationAvaliable
        ]);
    }

    // public  sendDebugNotification($user_id)
}
