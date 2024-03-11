<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\Holiday;
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

        $user = $request->user();


        $sapTry = SapTry::with('sapInstalation')->where('id', $id)->first();
        $sap_instalation_id = $sapTry->sap_instalation_id;

        if ($user->role_id == 5 && Carbon::parse($sapTry->start_datetime)->diffInMinutes(Carbon::now()) >= 60) {
            return ApiResponseController::response('No puedes modificar una instalación que ya ha pasado mas de una hora', 400);
        }

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
        $sapInstalationNext = SapInstalation::where('id', '>', $sap_instalation_id)->where('order_id', $sapTry->sapInstalation->order_id)->first();


        if ($request->status === 'Reprogramada' && !$sapTryNext && !$sapInstalationNext) {

            $sapInstalationsCount = Order::where('id', $sapTry->sapInstalation->order_id)->with('sapInstalations')->first()->sapInstalations->count();
            $sapStryCount = SapTry::where('sap_instalation_id', $sap_instalation_id)->get()->count();

            if ($sapStryCount === 3 && $sapTry->sapInstalation->instalation_type != 'Desbloqueo SAP') {

                SapInstalation::where('id', $sap_instalation_id)->update(['status' => 'Cancelada']);
                // create new sap instalation with the same data
                $sapInstalation                   = new SapInstalation();
                // get fillable fields
                $fillableFields                   = $sapInstalation->getFillable();
                foreach ($fillableFields as $field) {
                    $sapInstalation->$field = $sapTry->sapInstalation->$field;
                }

                $otherSaps = SapInstalation::where('order_id', $sapTry->sapInstalation->order_id)
                    ->where(function ($query) {
                        $query->where('instalation_type', '<>', 'Desbloqueo')
                            ->where('instalation_type', '<>', 'Asignación de usuario y contraseña')
                            ->orWhereNull('instalation_type');
                    })
                    ->get()
                    ->count();


                $sapInstalation->status = 'Pendiente';
                $sapInstalation->key    = md5(microtime());
                $sapInstalation->save();

                $sap_instalation_id = $sapInstalation->id;
            }


            $try = new SapTry();
            $start_datetime = Carbon::now()->addDays(2);


            $holidays = Holiday::all();
            while ($start_datetime->isSunday() || $holidays->contains('date', $start_datetime->format('Y-m-d'))) {
                $start_datetime->addDay();
            }


            $try->start_datetime = $start_datetime->format('Y-m-d') . ' 00:00:00';
            $try->end_datetime = Carbon::parse($try->start_datetime)->addMinutes(30);


            $try->staff_id           = SapInstalationsController::findAvailableStaff($try->start_datetime)->id;
            $try->sap_instalation_id = $sap_instalation_id;
            $try->status             = "Por programar";
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

        // is today
        if ($sapTry->wasChanged('staff_id') && Carbon::parse($sapTry->start_datetime)->format('Y-m-d') === Carbon::now()->format('Y-m-d')) {
            $student = Student::where('id', $sapTry->sapInstalation->order->student->id)->with('city', 'state')->first();

            $noti = new NotificationController();
            $noti = $noti->store([
                'title'      => 'Asignación de instalación SAP',
                'body'       => 'Se te ha asignado una instalación para el día ' . Carbon::parse($sapTry->start_datetime)->format('d/m/Y') . ' a las ' . Carbon::parse($sapTry->start_datetime)->format('H:i') . ' para el alumno ' . $student->name,
                'icon'       => 'check_circle_outline',
                'url'        => '#',
                'user_id'    => $request->staff_id,
                'use_router' => false,
                'custom_data' => [
                    []
                ]
            ]);
        }




        return ApiResponseController::response('Sap instalation tries', 200, $sapTry);
    }
}
