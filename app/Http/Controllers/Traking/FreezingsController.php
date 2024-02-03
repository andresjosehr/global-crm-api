<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\DatesHistory;
use App\Models\Freezing;
use App\Models\OrderCourse;
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
        if(count($request->all()) == 0) {
            return ApiResponseController::response('No hay datos', 422);
        }

        $datesFields = ['start_date', 'finish_date', 'return_date', 'payment_date', 'new_return_date', 'new_finish_date'];
        // return $request->all();
        foreach($request->all() as $free) {


            $fillable = (new Freezing())->getFillable();
            $free2 = array_filter($free, function($key) use ($fillable) {
                return in_array($key, $fillable);
            }, ARRAY_FILTER_USE_KEY);

            foreach($datesFields as $date) {
                if(isset($free2[$date])) {
                    $free2[$date] = Carbon::parse($free2[$date])->format('Y-m-d');
                }
            }

            $order_course_id = $request->all()[0]['order_course_id'];

            // Check if current date is between start_date and return_date
            if(isset($free2['start_date']) && isset($free2['return_date'])) {

                $dateHistory = DatesHistory::where('freezing_id', $free['id'])->first();
                if(!$dateHistory) {
                    $orderCourse = OrderCourse::where('id', $free['order_course_id'])->first();
                    DatesHistory::create([
                        'order_id'        => $orderCourse->order_id,
                        'order_course_id' => $free['order_course_id'],
                        'start_date'      => $orderCourse->start,
                        'end_date'        => $free2['finish_date'],
                        'freezing_id'     => $free['id'],
                        'type'            => 'Congelamiento',
                    ]);
                    // Get date_history created
                    $dateHistory = DatesHistory::where('freezing_id', $free['id'])->first();

                    OrderCourse::where('id', $free2['order_course_id'])->update([
                        'end' => $dateHistory->end_date
                    ]);
                }

                $currentDate = Carbon::now();
                $startDate   = Carbon::parse($free2['start_date']);
                $returnDate  = Carbon::parse($free2['return_date']);
                if($currentDate->between($startDate, $returnDate)) {
                    OrderCourse::where('id', $free['order_course_id'])->update(['classroom_status' => 'Congelado']);
                }
            }

            Freezing::where('id', $free['id'])->update($free2);

            if(isset($free2['start_date']) && isset($free2['return_date'])) {

                $freezingDB = Freezing::where('id', $free['id'])->first();
                if($freezingDB->mail_status == 'Pendiente') {
                    self::scheduleMail($freezingDB, $order_course_id);
                }
            }
        }


        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $order_course_id)->orderBy('id', 'desc')->first();
        $now = Carbon::now();
        $courseFreezing = false;
        if($now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Congelado']);
            $courseFreezing = true;
        }

        return ApiResponseController::response('Exito', 200, ['freezing' => $courseFreezing]);
    }

    public function unfreezeCourse($id)
    {
        OrderCourse::where('id', $id)->update(['classroom_status' => 'Cursando']);
        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $id)->orderBy('id', 'desc')->first();

        if(!$lastFreezing) {
            return ApiResponseController::response('No hay congelamientos', 422);
        }

        $now = Carbon::now();
        if(!$now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            return ApiResponseController::response('El curso no esta congelado', 422);
        }

        // Update return_date
        $newReturnDate = Carbon::now();
        $newFinishDate = Carbon::parse($lastFreezing->finish_date);
        $diff = $newReturnDate->diffInDays(Carbon::parse($lastFreezing->return_date));
        $newFinishDate->subDays($diff);

        Freezing::where('id', $lastFreezing->id)->update(['new_return_date' => $newReturnDate, 'new_finish_date' => $newFinishDate]);

        $lastFreezing = Freezing::where('order_course_id', $id)->orderBy('id', 'desc')->first();


        return ApiResponseController::response('Exito', 200, $lastFreezing);

    }

    public function scheduleMail($freezing, $order_course_id){
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
        })-1;
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

        Log::info('Fecha de inicio: ' . Carbon::parse($freezing->start_date)->format('Y-m-d'));
        Log::info('Fecha actual: ' . Carbon::now()->format('Y-m-d'));

        if(Carbon::parse($freezing->start_date)->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
            Freezing::where('id', $freezing->id)->update(['mail_status' => 'Enviado']);
        }

        if(Carbon::parse($freezing->start_date) > Carbon::now()) {
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
