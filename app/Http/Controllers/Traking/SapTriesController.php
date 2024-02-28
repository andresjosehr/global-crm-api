<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Price;
use App\Models\SapInstalation;
use App\Models\SapTry;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SapTriesController extends Controller
{
    public function getSapTries(Request $request, $id)
    {
        $sapTry = SapTry::with('staff', 'sapInstalation')->where('sap_instalation_id', $id)->get();

        return ApiResponseController::response('Sap instalation tries', 200, $sapTry);
    }

    public function getSapTry(Request $request, $id)
    {
        $sapTry = SapTry::with('staff', 'sapInstalation')->where('id', $id)->first();

        $sapTry->student = Student::where('id', $sapTry->sapInstalation->order->student->id)->with('city', 'state')->first();

        return ApiResponseController::response('Sap instalation tries', 200, $sapTry);
    }

    public function update(Request $request, $id)
    {
        $sapTry = SapTry::with('sapInstalation')->where('id', $id)->first();
        $sap_instalation_id = $sapTry->sap_instalation_id;

        if ($request->time) {
            $sapTry->start_datetime = Carbon::parse($request->date)->format('Y-m-d') . ' ' . $request->time['start_time'];
            $sapTry->end_datetime = Carbon::parse($sapTry->start_datetime)->addMinutes(30);
            $sapTry->start_datetime_target_timezone = Carbon::parse($sapTry->date)->format('Y-m-d') . ' ' . $request->time['start_time_in_target_zone'];
        } else {
            $time = Carbon::parse($sapTry->start_datetime)->format('H:i:s');
            $sapTry->start_datetime = Carbon::parse($request->date)->format('Y-m-d') . ' ' . $time;
            $sapTry->end_datetime = Carbon::parse($sapTry->start_datetime)->addMinutes(30);
        }



        // Check if there is a sap try with id graten than current sap try
        $sapTryNext = SapTry::where('sap_instalation_id', $sap_instalation_id)->where('id', '>', $id)->first();
        $sapInstalationNext = SapInstalation::where('id', '>', $sap_instalation_id)->first();

        if ($request->status === 'Reprogramada' && !$sapTryNext && !$sapInstalationNext) {

            $sapInstalationsCount = Order::where('id', $sapTry->sapInstalation->order_id)->with('sapInstalations')->first()->sapInstalations->count();
            $sapStryCount = SapTry::where('sap_instalation_id', $sap_instalation_id)->get()->count();

            if ($sapInstalationsCount === 1 && $sapStryCount === 3) {
                SapInstalation::where('id', $sap_instalation_id)->update(['status' => 'Cancelada']);

                // create new sap instalation with the same data
                $sapInstalation                   = new SapInstalation();
                // get fillable fields
                $fillableFields                   = $sapInstalation->getFillable();
                foreach ($fillableFields as $field) {
                    $sapInstalation->$field = $sapTry->sapInstalation->$field;
                }
                $sapInstalation->status = 'Pendiente';
                $sapInstalation->key    = md5(microtime());
                $sapInstalation->save();

                $sap_instalation_id = $sapInstalation->id;
            }


            $try = new SapTry();
            $try->start_datetime = Carbon::now()->addDays(2)->format('Y-m-d') . ' 00:00:00';
            $try->end_datetime = Carbon::parse($try->start_datetime)->addMinutes(30);

            $try->staff_id           = SapInstalationsController::findAvailableStaff($try->start_datetime)->id;
            $try->sap_instalation_id = $sap_instalation_id;
            $try->status             = "Por programar";
            $try->payment_enabled    = SapTry::where('sap_instalation_id', $sap_instalation_id)->count() >= 3  ? 1 : 0;
            $try->save();
        }

        if ($sapTry->status === 'Realizada') {
            $sapInstalation = SapInstalation::find($sap_instalation_id);
            $sapInstalation->status = 'Realizada';
            $sapInstalation->save();
        }

        $sapTry->status = $request->status;
        $sapTry->staff_id = $request->staff_id;
        $sapTry->save();


        if ($sapTry->payment_enabled) {
            self::updatePayment($request, $id);
        }

        return ApiResponseController::response('Sap instalation tries', 200, $sapTry);
    }


    public function updatePayment(Request $request, $id)
    {
        $sapTry = SapTry::find($id);
        // only payment_fields
        $data = array_filter($request->all(), function ($key) {
            return in_array($key, ['price_id', 'currency_id', 'payment_receipt', 'payment_method_id', 'payment_date']);
        }, ARRAY_FILTER_USE_KEY);

        $sapTry->fill($data);

        if ($data['price_id']) {
            $sapTry->price_amount = Price::find($data['price_id'])->amount;
        }

        $student = Student::with('userAssigned')->where('id', $sapTry->sapInstalation->order->student->id)->with('city', 'state')->first();
        $user = $student->userAssigned[0];

        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => 'Pago de agendamiento de instalación',
            'body'       => 'El alumno ' . $student->name . ' ha realizado el pago de la instalación, por favor revisar el comprobante de pago y confirmar la instalación.',
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => $user->id,
            'use_router' => false,
            'custom_data' => [
                []
            ]
        ]);

        $sapTry->save();

        return ApiResponseController::response('Sap try instalation updated', 200, $sapTry);
    }

    public function verifiedPayment(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }

        $sapTry = SapTry::find($id);
        $sapTry->payment_verified_at = Carbon::now();
        $sapTry->payment_verified_by = $user->id;
        $sapTry->save();

        return ApiResponseController::response('Sap try instalation updated', 200, $sapTry);
    }
}
