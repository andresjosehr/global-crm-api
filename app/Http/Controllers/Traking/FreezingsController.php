<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\DatesHistory;
use App\Models\Freezing;
use App\Models\OrderCourse;
use Carbon\Carbon;
use Google\Service\AdExchangeBuyerII\Date;
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

            // Check if current date is between start_date and return_date
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
                    self::scheduleMail($freezingDB, $order_course_id);
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

        $allFreezings = Freezing::where('order_course_id', $order_course_id)->get();


        return ApiResponseController::response('Exito', 200, ['freezing' => $courseFreezing, 'allFreezings' => $allFreezings]);
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

        $last_freezing = Freezing::where('order_course_id', $order_course_id)->orderBy('id', 'desc')->first();


        return ApiResponseController::response('Exito', 200, $last_freezing);
    }

    public function moveNextCoursesDate(Freezing $freezing, $to = 'forward')
    {
        $finish_date = $to == 'forward' ? $freezing->finish_date : $freezing->new_finish_date;
        $orderCourse = OrderCourse::where('id', $freezing->order_course_id)->first();
        $nextCourses = OrderCourse::where('order_id', $orderCourse->order_id)->where('id', '>', $orderCourse->id)->where('type', 'paid')->orderBy('id', 'asc')->get();
        if (count($nextCourses) == 5 || count($nextCourses) == 0) {
            return;
        }

        if($to == 'backward'){
            OrderCourse::where('id', $freezing->order_course_id)->update(['end' => $finish_date]);
        }

        $finish_date = Carbon::parse($finish_date);
        foreach ($nextCourses as $order_course) {
            Log::info('Fecha final: ' . $finish_date);
            $newStartDate = Carbon::parse($finish_date)->addDays(1);
            $newEndDate = Carbon::parse($newStartDate)->addMonths(3);
            $finish_date = $newEndDate;
            OrderCourse::where('id', $order_course->id)->update(['start' => $newStartDate, 'end' => $newEndDate]);
            DatesHistory::create([
                'order_course_id' => $order_course->id,
                'start_date'      => $newStartDate,
                'end_date'        => $newEndDate,
                'type'            => 'Congelamiento de un curso anterior a este',
            ]);
        }

        Log::info('Se movieron los cursos hacia adelante');

        return true;
    }


    public function scheduleMail($freezing, $order_course_id)
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
            'order_course'  => $order_course,
            'student'       => $student,
            'dates_history' => $dates_history,
            'original_date' => $original_date,
            'course'        => $course,
            'freezing'      => $freezing,
            'remainFreezingDurationAvaliable' => $remainFreezingDurationAvaliable
        ])->render();

        $scheduleTime = null;

        if (Carbon::parse($freezing->start_date)->format('Y-m-d') <= Carbon::now()->format('Y-m-d')) {
            Freezing::where('id', $freezing->id)->update(['mail_status' => 'Enviado']);
        }

        if (Carbon::parse($freezing->start_date) > Carbon::now()) {
            $scheduleTime = Carbon::parse($freezing->start_date)->format('m/d/Y');
            Freezing::where('id', $freezing->id)->update(['mail_status' => 'Programado']);
        }

        CoreMailsController::sendMail(
            'andresjosehr@gmail.com',
             'Has congelado tu curso',
            $content,
            $scheduleTime
        );

    }
}
