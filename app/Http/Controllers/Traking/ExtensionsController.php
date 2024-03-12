<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Due;
use App\Models\Extension;
use App\Models\OrderCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExtensionsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $sapInstalation = new Extension();
        $sapInstalation->fill($request->all());
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation saved', 200, $sapInstalation);
    }


    public function update(Request $request, $id)
    {

        $cert     = $request->all();
        $fillable = (new Extension())->getFillable();
        $cert     = array_filter($cert, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);


        if (gettype($cert['months']) == 'integer') {

            if (!DatesHistory::where('extension_id', $cert['id'])->first()) {

                $orderCourse = OrderCourse::where('id', $cert['order_course_id'])->first();

                $holidays = DatesHistory::all();
                $end = Carbon::parse($orderCourse->end)->addMonths($cert['months']);
                while ($holidays->contains('date', $orderCourse->end) || Carbon::parse($orderCourse->end)->isSunday()) {
                    $end = Carbon::parse($orderCourse->end)->addDay();
                }


                $otherOrderCourses = OrderCourse::with('course')->where('order_id', $cert['order_id'])->where('start', '>', $orderCourse->start)->whereType($orderCourse->type)->get();

                Log::info($otherOrderCourses);



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

                    Log::info("Curso a extender: " . $otherOrderCourse->course->name . ' Fecha de inicio: ' . $start->format('Y-m-d') . ' Fecha de fin: ' . $end->format('Y-m-d'));

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
        }

        Extension::where('id', $cert['id'])->update($cert);

        $extension = Extension::where('id', $cert['id'])->first();

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
        $extension = Extension::with('order.student', 'orderCourse.course')->where('id', $extension_id)->first();

        if ($extension->notification_sent_at) {
            return ApiResponseController::response('Notificacion ya enviada', 200);
        }


        $content = view("mails.extension")->with(['extension' => $extension])->render();


        $mail = [
            'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
            'to'         => [$extension->order->student->email],
            'subject'    => 'Notificación de extensión de curso',
            'student_id' => $extension->order->student->id,
            'html'       => $content
        ];

        ResendService::sendSigleMail($mail);

        $text = "Hola, le informo que hemos completado el proceso administrativo de extensión de su curso: " . $extension->orderCourse->course->name . ". \nSu nueva fecha de fin sería: " . $extension->orderCourse->end . ".\nLe hemos enviado la misma información a su correo registrado: " . $extension->order->student->email . ".\nAsimismo, tiene información relevante sobre la aprobación de su examen de certificación correspondiente.";

        $params = [
            'student_id' => $extension->order->student->id,
            'phone' => $extension->order->student->phone,
            'message' => $text,
        ];

        GeneralJob::dispatch(ExtensionsController::class, 'sendLiveConnectMessage', $params)->onQueue('liveconnect');

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
