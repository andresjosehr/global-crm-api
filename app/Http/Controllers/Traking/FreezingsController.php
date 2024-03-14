<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DuesController;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Due;
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


    public function update(Request $request, $id)
    {

        $datesFields = ['start_date', 'finish_date', 'return_date', 'payment_date', 'new_return_date', 'new_finish_date'];

        // remove fue_id
        $data = $request->all();
        unset($data['due_id']);

        $fillable = (new Freezing())->getFillable();
        $free     = array_filter($data, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($datesFields as $date) {
            if (isset($free[$date])) {
                $free[$date] = Carbon::parse($free[$date])->format('Y-m-d');
            }
        }


        Freezing::where('id', $id)->update($free);
        $freezingDB = Freezing::where('id', $id)->first();

        if (!$freezingDB->due_id && $request->due_id == 1) {
            $due = Due::create([
                'payment_reason' => 'Congelación',
                'student_id' => $freezingDB->orderCourse->order->student_id,
            ]);

            $freezingDB->due_id = $due->id;
            $freezingDB->save();
        }

        if ($freezingDB->due_id && $freezingDB->due_id != 1) {
            $dueController = new DuesController();
            $dueController->updatePayment($request, $freezingDB->due_id);
        }

        $freezingDB = Freezing::where('id', $id)->first();
        Log::info($freezingDB);

        // Check if start_date and return_date exists
        $dateHistory = DatesHistory::where('freezing_id', $id)->first();

        if ($freezingDB->due_id == null && !$freezingDB->set) {
            self::setFreezing($freezingDB);
        }



        $freezing = Freezing::with('orderCourse', 'due')->where('id', $id)->first();


        return ApiResponseController::response('Exito', 200, ['freezing' => $freezing]);
    }

    // public function calculateCharges($currentFreezingId)
    // {
    //     $currentFreezing = Freezing::find($currentFreezingId);
    //     $totalMonths = Freezing::where('id', '!=', $currentFreezingId)->where('order_course_id', $currentFreezing->order_course_id)
    //         ->get()
    //         ->sum(function ($freezing) {
    //             return intval($freezing->months);
    //         });

    //     $prevMonths = $totalMonths - intval($currentFreezing->months);
    //     $monthToCharge = $prevMonths >= 3 ? intval($currentFreezing->months) : max(0, intval($currentFreezing->months) - (3 - $prevMonths));

    //     if ($totalMonths <= 3 || $monthToCharge <= 0) {
    //         $currentFreezing->due_id = null;
    //         $currentFreezing->save();
    //     } else {

    //         if (!$currentFreezing->due_id) {
    //             $due = Due::create([
    //                 'payment_reason' => 'Congelación',
    //                 'student_id' => $currentFreezing->orderCourse->order->student_id,
    //             ]);

    //             $currentFreezing->due_id = $due->id;
    //             $currentFreezing->save();
    //         }
    //     }
    // }




    static public function setFreezing($freezing)
    {
        $orderCourse = OrderCourse::where('id', $freezing->order_course_id)->first();
        DatesHistory::create([
            'order_id'        => $orderCourse->order_id,
            'order_course_id' => $freezing->order_course_id,
            'start_date'      => $orderCourse->start,
            'end_date'        => $freezing->finish_date,
            'freezing_id'     => $freezing->id,
            'type'            => 'Congelamiento',
        ]);


        // Get date_history created
        $dateHistory = DatesHistory::where('freezing_id', $freezing->id)->first();

        OrderCourse::where('id', $freezing->order_course_id)->update([
            'end' => $dateHistory->end_date
        ]);

        self::sendFreezeMail($freezing, $freezing->order_course_id);

        $now = Carbon::now();
        if ($now->between(Carbon::parse($freezing->start_date), Carbon::parse($freezing->finish_date))) {
            OrderCourse::where('id', $freezing->order_course_id)->update(['classroom_status' => 'Congelado']);
        }

        if ($freezing) {
            $freeDB = Freezing::where('id', $freezing->id)->first();
            self::moveNextCoursesDate($freeDB);
        }

        $currentDate = Carbon::now();
        $startDate   = Carbon::parse($freezing->start_date);
        $returnDate  = Carbon::parse($freezing->return_date);
        if ($currentDate->between($startDate, $returnDate)) {
            OrderCourse::where('id', $freezing->order_course_id)->update(['classroom_status' => 'Congelado']);
        }
        $freezing->set = true;
        $freezing->save();
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

        self::sendUnfreezingEmail($last_freezing);

        return ApiResponseController::response('Exito', 200, $last_freezing);
    }

    static public function moveNextCoursesDate(Freezing $freezing, $to = 'forward')
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


    static public function sendFreezeMail($freezing, $order_course_id)
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

        GeneralJob::dispatch(FreezingsController::class, 'sendLiveConnectMessage', [
            'phone'      => $student->phone,
            'message'    => "Le he enviado el correo con la información sobre su curso y sus nuevas fechas de inicio, si tiene dudas por favor me contacta por este medio.\n Le recuerdo que debe *mantener su conexión a SAP y anotar su contraseña* para no olvidarla, *ya que no mantenemos el respaldo de la misma.*",
            'student_id' => $student->id,
            'type'       => 'FREEZING_NOTIFICATION'
        ])->onQueue('liveconnect');
    }

    public function sendLiveConnectMessage($phone, $message, $student_id, $type)
    {
        $liveconnectService = new LiveConnectService();
        $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', $type, 1);
        sleep(rand(12, 20));
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
