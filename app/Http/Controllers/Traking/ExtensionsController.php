<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Due;
use App\Models\Extension;
use App\Models\Holiday;
use App\Models\OrderCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtensionsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $extension = new Extension();

        $extension->fill($request->all());

        $orderCourses = OrderCourse::where('order_id', $request->order_id)->where('type', 'paid')->get();
        $extension->courses = $orderCourses->count() == 5 ? 'all' : 'single';

        $orderCoursesSync = $extension->courses == 'all' ? $orderCourses->pluck('id') : collect([$request->order_course_id]);
        $orderCoursesSync = $orderCoursesSync->mapWithKeys(function ($item) use ($request) {
            return [$item => ['order_id' => $request->order_id]];
        });


        $extension->save();

        $extension->orderCourses()->sync($orderCoursesSync);

        $extension = Extension::where('id', $extension->id)->first();

        return ApiResponseController::response('Sap instalation saved', 200, $extension);
    }


    public function update(Request $request, $id)
    {

        $cert     = $request->all();
        $fillable = (new Extension())->getFillable();
        $cert     = array_filter($cert, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        Extension::where('id', $cert['id'])->update($cert);

        $extension = Extension::where('id', $cert['id'])->first();

        $orderCoursesSync = $extension->courses == 'all' ? collect($request->order_course_ids) : collect([$request->order_course_id]);
        $orderCoursesSync = $orderCoursesSync->mapWithKeys(function ($item) use ($request) {
            return [$item => ['order_id' => $request->order_id]];
        });

        $extension->orderCourses()->sync($orderCoursesSync);


        $holidays = Holiday::all();
        $extensionDB = Extension::where('id', $cert['id'])->first();
        if (gettype($cert['months']) == 'integer') {

            if (!DatesHistory::where('extension_id', $cert['id'])->first()) {

                if ($extensionDB->courses === 'single') {

                    $orderCourse = OrderCourse::where('id', $cert['order_course_id'])->first();


                    $end = Carbon::parse($orderCourse->end)->addMonths($cert['months']);
                    while ($holidays->contains('date', $orderCourse->end) || Carbon::parse($orderCourse->end)->isSunday()) {
                        $end = Carbon::parse($orderCourse->end)->addDay();
                    }


                    $otherOrderCourses = OrderCourse::with('course')->where('order_id', $cert['order_id'])->where('start', '>', $orderCourse->start)->whereType($orderCourse->type)->get();

                    DatesHistory::create([
                        'order_id'        => $cert['order_id'],
                        'order_course_id' => $cert['order_course_id'],
                        'start_date'      => $orderCourse->start,
                        'end_date'        => $end->format('Y-m-d'),
                        'extension_id'    => $cert['id'],
                        'type'            => 'Extension',
                    ]);

                    // Get date_history created
                    $dateHistory = DatesHistory::where('extension_id', $cert['id'])->first();

                    OrderCourse::where('id', $cert['order_course_id'])->update([
                        'end' => $dateHistory->end_date
                    ]);

                    $end = $dateHistory->end_date;
                    $otherOrderCourses->each(function ($otherOrderCourse) use ($holidays, &$end) {
                        $licenses = [
                            '3 meses' => 3,
                            '6 meses' => 6,
                            '12 meses' => 12,
                        ];

                        $start = Carbon::parse($end)->addDay();

                        while ($holidays->contains('date', $start) || Carbon::parse($start)->isSunday()) {
                            $start = Carbon::parse($start)->addDay();
                        }

                        $end = Carbon::parse($start)->addMonths($licenses[$otherOrderCourse->license]);

                        while ($holidays->contains('date', $end) || Carbon::parse($end)->isSunday()) {
                            $end = Carbon::parse($end)->addDay();
                        }


                        $otherOrderCourse->start = $start->format('Y-m-d');
                        $otherOrderCourse->end = $end->format('Y-m-d');

                        $otherOrderCourse->save();

                        DatesHistory::create([
                            'order_id'        => $otherOrderCourse->order_id,
                            'order_course_id' => $otherOrderCourse->id,
                            'start_date'      => $start->format('Y-m-d'),
                            'end_date'        => $end->format('Y-m-d'),
                            'type'            => 'Extension de un curso anterior',
                        ]);
                    });
                }

                if ($extensionDB->courses === 'all') {
                    $orderCourses = Extension::where('id', $cert['id'])->first()->orderCourses;
                    foreach ($orderCourses as $orderCourse) {
                        $end = Carbon::parse($orderCourse->end)->addMonths($cert['months']);
                        while ($holidays->contains('date', $orderCourse->end) || Carbon::parse($orderCourse->end)->isSunday()) {
                            $end = Carbon::parse($orderCourse->end)->addDay();
                        }

                        $orderCourse->end = $end->format('Y-m-d');
                        $orderCourse->save();

                        DatesHistory::create([
                            'order_id'        => $orderCourse->order_id,
                            'order_course_id' => $orderCourse->id,
                            'start_date'      => $orderCourse->start,
                            'end_date'        => $end->format('Y-m-d'),
                            'extension_id'    => $cert['id'],
                            'type'            => 'Extension',
                        ]);
                    }
                }

                // Notification
                $noti = new NotificationController();
                $noti = $noti->store([
                    'title'      => 'Se ha registrado un pago de extensión',
                    'body'       => 'Se ha registrado un pago de extensión del alumno ' . $extensionDB->order->student->name . ' Por favor revisar el pago',
                    'icon'       => 'check_circle_outline',
                    'url'        => '#',
                    'user_id'    => 10,
                    'use_router' => false,
                ]);
            }
        }



        self::updatePayment($request, $extension->due_id);

        return ApiResponseController::response('Exito', 200);
    }

    public function updatePayment(Request $request, $due_id)
    {

        $due = Due::find($due_id);
        // Get fillable fields
        $fillable = (new Due())->getFillable();
        $data = array_filter($request->all(), function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        $due->fill($data);

        $due->save();

        return true;
    }

    static public function sendNotificacion($extension_id)
    {
        $extension = Extension::with('order.student', 'orderCourses.course')->where('id', $extension_id)->first();

        if ($extension->notification_sent_at) {
            return ApiResponseController::response('Notificacion ya enviada', 200);
        }

        foreach ($extension->orderCourses as $orderCourse) {






            $content = view("mails.extension")->with(['extension' => $extension, 'orderCourse' => $orderCourse])->render();


            $mail = [[
                'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
                'to'         => [$extension->order->student->email],
                'subject'    => 'Notificación de extensión de curso',
                'student_id' => $extension->order->student->id,
                'html'       => $content
            ]];

            ResendService::sendBatchMail($mail);

            $text = "Hola, le informo que hemos completado el proceso administrativo de extensión de su curso: " . $orderCourse->course->name . ". \nSu nueva fecha de fin sería: " . $orderCourse->end . ".\nLe hemos enviado la misma información a su correo registrado: " . $orderCourse->order->student->email . ".\nAsimismo, tiene información relevante sobre la aprobación de su examen de certificación correspondiente.";

            $params = [
                'student_id' => $extension->order->student->id,
                'phone' => $extension->order->student->phone,
                'message' => $text,
            ];

            GeneralJob::dispatch(ExtensionsController::class, 'sendLiveConnectMessage', $params)->onQueue('liveconnect');
        }

        $extension->notification_sent_at = now();
        $extension->save();

        return ApiResponseController::response('Notificacion enviada', 200);
    }

    public function sendLiveConnectMessage($student_id, $phone, $message)
    {
        $liveconnectService = new LiveConnectService();
        $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'PROCESSED_EXTENSION', 1);
        sleep(rand(12, 20));
    }
}
