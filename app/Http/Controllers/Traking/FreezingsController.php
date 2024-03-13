<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ResendService;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Freezing;
use App\Models\Holiday;
use App\Models\OrderCourse;
use App\Models\Process;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FreezingsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $freezing = new Freezing();
        $freezing->order_id = $request->order_id;
        $freezing->order_course_id = $request->order_course_id;

        $freezing->save();

        return ApiResponseController::response('Freezing saved', 200, $freezing);
    }


    public function update(Request $request)
    {
        // return Carbon::parse($request->all()[0]['finish_date'])->format('Y-m-d');
        if (count($request->all()) == 0) {
            return ApiResponseController::response('No hay datos', 422);
        }

        $datesFields = ['start_date', 'finish_date', 'return_date', 'payment_date', 'new_return_date', 'new_finish_date'];
        $freezingToMove = null;
        // return $request->all();
        foreach ($request->all() as $free) {


            $fillable = (new Freezing())->getFillable();
            $free_id = $free['id'];
            $free = array_filter($free, function ($key) use ($fillable) {
                return in_array($key, $fillable);
            }, ARRAY_FILTER_USE_KEY);

            foreach ($datesFields as $date) {
                if (isset($free[$date])) {
                    $free[$date] = Carbon::parse($free[$date])->format('Y-m-d');
                }
            }

            $order_course_id = $request->all()[0]['order_course_id'];

            // Check if start_date and return_date exists
            if (isset($free['start_date']) && isset($free['return_date'])) {

                $dateHistory = DatesHistory::where('freezing_id', $free_id)->first();
                if (!$dateHistory) {
                    $orderCourse = OrderCourse::where('id', $free['order_course_id'])->first();
                    DatesHistory::create([
                        'order_id'        => $orderCourse->order_id,
                        'order_course_id' => $free['order_course_id'],
                        'start_date'      => $orderCourse->start,
                        'end_date'        => $free['finish_date'],
                        'freezing_id'     => $free_id,
                        'type'            => 'Congelamiento',
                    ]);


                    // Get date_history created
                    $dateHistory = DatesHistory::where('freezing_id', $free_id)->first();

                    OrderCourse::where('id', $free['order_course_id'])->update([
                        'end' => $dateHistory->end_date
                    ]);

                    $freezingToMove = $free_id;
                }

                $currentDate = Carbon::now();
                $startDate   = Carbon::parse($free['start_date']);
                $returnDate  = Carbon::parse($free['return_date']);
                if ($currentDate->between($startDate, $returnDate)) {
                    OrderCourse::where('id', $free['order_course_id'])->update(['classroom_status' => 'Congelado']);
                }
            }

            Freezing::where('id', $free_id)->update($free);

            if (isset($free['start_date']) && isset($free['return_date'])) {

                $freezingDB = Freezing::where('id', $free_id)->first();
                if ($freezingDB->mail_status == 'Pendiente') {
                    self::sendFreezeMail($freezingDB, $order_course_id);
                }
            }
        }


        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $order_course_id)->orderBy('id', 'desc')->first();
        $now = Carbon::now();
        $courseFreezing = false;
        if ($now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Congelado']);
            $courseFreezing = true;
        }

        if ($freezingToMove) {
            $freeDB = Freezing::where('id', $freezingToMove)->first();
            self::moveNextCoursesDate($freeDB);
        }

        $freezings = Freezing::where('order_course_id', $order_course_id)->get();

        $order_courses = OrderCourse::where('order_id', $freezings[0]->order_id)->where('type', 'paid')->get();


        return ApiResponseController::response('Exito', 200, ['freezing' => $courseFreezing, 'freezings' => $freezings, 'order_courses' => $order_courses]);
    }



    public function unfreezeCourse($order_course_id)
    {
        OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Cursando']);
        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $order_course_id)->orderBy('id', 'desc')->first();

        if (!$lastFreezing) {
            return ApiResponseController::response('No hay congelamientos', 422);
        }

        $now = Carbon::now();
        if (!$now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            return ApiResponseController::response('El curso no esta congelado', 422);
        }

        // Update return_date
        $newReturnDate = Carbon::now();
        $newFinishDate = Carbon::parse($lastFreezing->finish_date);
        $diff = $newReturnDate->diffInDays(Carbon::parse($lastFreezing->return_date));
        $newFinishDate->subDays($diff);

        Freezing::where('id', $lastFreezing->id)->update(['new_return_date' => $newReturnDate, 'new_finish_date' => $newFinishDate]);

        DatesHistory::create([
            'order_course_id' => $order_course_id,
            'start_date'      => OrderCourse::where('id', $order_course_id)->first()->start,
            'end_date'        => $newFinishDate,
            'type'            => 'Descongelamiento',
            'freezing_id'     => $lastFreezing->id
        ]);

        $freezingDB = Freezing::where('id', $lastFreezing->id)->first();
        self::moveNextCoursesDate($freezingDB, 'backward');

        $last_freezing = Freezing::where('order_course_id', $order_course_id)
            ->with('orderCourse.course', 'orderCourse.order.currency', 'orderCourse.order.orderCourses', 'orderCourse.order.student')
            ->orderBy('id', 'desc')->first();

        $order_courses = OrderCourse::where('order_id', $last_freezing->order_id)->where('type', 'paid')->get();

        $params = [
            'freezing' => $last_freezing
        ];
        GeneralJob::dispatch(FreezingsController::class, 'sendUnfreezingEmail', $params)->onQueue('default');


        return ApiResponseController::response('Exito', 200, ['last_freezing' => $last_freezing, 'order_courses' => $order_courses]);
    }

    public function moveNextCoursesDate(Freezing $freezing, $to = 'forward')
    {
        $finish_date = $to == 'forward' ? $freezing->finish_date : $freezing->new_finish_date;
        $orderCourse = OrderCourse::where('id', $freezing->order_course_id)->first();
        $nextCourses = OrderCourse::where('order_id', $orderCourse->order_id)->where('start', '>', $orderCourse->start)->where('type', $orderCourse->type)->orderBy('start', 'asc')->get();
        if (count($nextCourses) == 5 || count($nextCourses) == 0) {
            return;
        }

        if ($to == 'backward') {
            OrderCourse::where('id', $freezing->order_course_id)->update(['end' => $finish_date]);
        }

        $finish_date = Carbon::parse($finish_date);
        foreach ($nextCourses as $order_course) {
            $newStartDate = Carbon::parse($finish_date)->addDays(1);

            $holidays = Holiday::all();
            // if new start date is a holiday or sunday, add one day
            while ($holidays->contains('date', $newStartDate->format('Y-m-d')) || $newStartDate->dayOfWeek == 0) {
                $newStartDate->addDays(1);
            }

            $newEndDate = Carbon::parse($newStartDate)->addMonths(3);

            while ($holidays->contains('date', $newEndDate->format('Y-m-d')) || $newEndDate->dayOfWeek == 0) {
                $newEndDate->addDays(1);
            }

            $finish_date = $newEndDate;
            OrderCourse::where('id', $order_course->id)->update(['start' => $newStartDate, 'end' => $newEndDate]);
            DatesHistory::create([
                'order_course_id' => $order_course->id,
                'start_date'      => $newStartDate,
                'end_date'        => $newEndDate,
                'type'            => $to == 'forward' ? 'Congelamiento de un curso anterior a este' : 'Descongelamiento de un curso anterior a este',
            ]);
        }


        return true;
    }


    public function sendFreezeMail($freezing, $order_course_id)
    {
        // Get user


        $order_course  = OrderCourse::where('id', $order_course_id)->with('dateHistory', 'course', 'order.student', 'freezings')->first();
        $student       = $order_course->order->student;
        $dates_history = $order_course->dateHistory;
        $course        = $order_course->course;
        $remainFreezingDurationAvaliable = 3 - $order_course->freezings->reduce(function ($carry, $item) {
            return $carry + $item->months;
        }, 0);

        // Get date history record before the freezing by id
        $dateRecord = DatesHistory::where('order_course_id', $order_course_id)->get();
        // Get index of the date history record before the freezing
        $index = $dateRecord->search(function ($item) use ($freezing) {
            return $item->freezing_id == $freezing->id;
        }) - 1;
        $original_date = $dateRecord[$index];


        $content = view('mails.freezing')->with([
            'order_course'                    => $order_course,
            'student'                         => $student,
            'dates_history'                   => $dates_history,
            'original_date'                   => $original_date,
            'course'                          => $course,
            'freezing'                        => $freezing,
            'remainFreezingDurationAvaliable' => $remainFreezingDurationAvaliable
        ])->render();

        Freezing::where('id', $freezing->id)->update(['mail_status' => 'Enviado']);

        $mail = [[
            'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
            'to'         => [$student->email],
            'subject'    => 'Tomalo con calma, ¡Congelamos tu curso!',
            'student_id' => $student->id,
            'html'    => $content
        ]];

        ResendService::sendBatchMail($mail);
    }

    public function sendUnfreezingEmail($freezing)
    {


        $mail = [[
            'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
            'to'         => [$freezing->orderCourse->order->student->email],
            'subject'    => 'Continúa con tu Capacitación de ' . $freezing->orderCourse->course->name . ' con ¡Global Tecnologías Academy!',
            'student_id' => $freezing->orderCourse->order->student->id,
            'html'    => view('mails.unfreezing_new')->with(['freezing' => $freezing])->render()
        ]];

        ResendService::sendBatchMail($mail);
    }

    public function dispatchMail($email, $subject, $content, $scheduleTime, $freezing_id)
    {
        $message = CoreMailsController::sendMail($email, $subject, $content, $scheduleTime);
        Freezing::where('id', $freezing_id)->update(['mail_id' => $message->messageId]);
    }
}
