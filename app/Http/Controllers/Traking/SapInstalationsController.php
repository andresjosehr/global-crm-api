<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\OrderCourse;
use App\Models\SapInstalation;
use App\Models\StaffAvailabilitySlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SapInstalationsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $sapInstalation = new SapInstalation();
        $sap = $request->all();

        $sap['key'] = md5(microtime());
        $sap['start_datetime'] = OrderCourse::where('order_id', $sap['order_id'])->where('type', 'paid')->get()->reduce(function ($carry, $item) {
            Log::info($item->start . ' - ' . $carry);
            return $item->start < $carry ? $item->start : $carry;
        }, Carbon::now()->addDecade()->format('Y-m-d'));
        $sap['staff_id'] = $this->findAvailableStaff($sap['start_datetime'])->id;


        $sapInstalation->fill($sap);
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation saved', 200, $sapInstalation);
    }

    public function checkDraft($sap)
    {
        $fields = [
            "restrictions",
            "sap_user",
            "screenshot",
            "start_datetime",
            "end_datetime",
            "operating_system",
            "pc_type",
            "status",
            "previus_sap_instalation",
            // "instalation_type",


        ];

        if ($sap['pc_type'] === 'Personal') {
            unset($fields[0]);
        }

        if ($sap['previus_sap_instalation'] === true) {
            unset($fields[1]);
            unset($fields[2]);
        }

        $allFieldsFilled = collect($fields)->reduce(function ($carry, $field) use ($sap) {
            $filled = $carry && array_key_exists($field, $sap) && !is_null($sap[$field]);
            Log::info($field . ' - ' . $sap[$field] . ' - ');
            Log::info($filled ? 'true' : 'false');
            return $filled;
        }, true);

        Log::info($allFieldsFilled ? 'true' : 'false');

        if (!$allFieldsFilled) {
            return true;
        }

        return false;
    }


    public function update(Request $request, $id)
    {
        $sapInstalation = SapInstalation::find($id);

        // return $request->time;
        $sap = $request->all();



        // remove key
        unset($sap['key']);

        $sap['start_datetime'] = Carbon::parse($sap['date'])->format('Y-m-d');
        $time = $request->time ? $request->time : '00:00:00';
        $sap['start_datetime'] = $sap['start_datetime'] . ' ' . $time;
        $sap['end_datetime'] = Carbon::parse($sap['start_datetime'])->addMinutes(30)->format('Y-m-d H:i:s');

        $sap['draft'] = self::checkDraft($sap);

        $sapInstalation->fill($sap);
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation updated', 200, $sapInstalation);
    }

    // checkScheduleAccess
    public function checkScheduleAccess(Request $request, $key)
    {
        $sapInstalation = SapInstalation::where('key', $key)->first();

        if (!$sapInstalation) {
            return ApiResponseController::response('Unauthorized', 401);
        }

        return ApiResponseController::response('Authorized', 200);
    }

    public function getSapInstalation(Request $request, $key)
    {
        $sapInstalation = SapInstalation::where('key', $key)->with('student.city', 'student.state')->first();

        if (!$sapInstalation) {
            return ApiResponseController::response('Unauthorized', 401);
        }

        return ApiResponseController::response('Authorized', 200, $sapInstalation);
    }

    public function findAvailableStaff($date)
    {
        // 1. Obtener todos los técnicos
        $technicians = User::where('role_id', 5)->get();


        $maxAvailableTime = 0;
        $availableTechnician = null;

        foreach ($technicians as $technician) {
            // 2. Filtrar por Disponibilidad
            $availabilitySlots = StaffAvailabilitySlot::where('user_id', $technician->id)
                ->where('day', strtolower(Carbon::parse($date)->format('l')))
                ->get();


            $totalAvailableTime = 0;

            foreach ($availabilitySlots as $slot) {
                // 3. Calcular Tiempo Disponible
                $startTime = Carbon::parse($slot->start_time);
                $endTime = Carbon::parse($slot->end_time);

                $instalations = SapInstalation::where('staff_id', $technician->id)
                    ->whereDate('start_datetime', $date)
                    ->get();

                $totalInstalationTime = 0;
                foreach ($instalations as $instalation) {
                    $instalationStartTime = Carbon::parse($instalation->start_datetime);
                    $instalationEndTime = Carbon::parse($instalation->end_datetime);
                    $totalInstalationTime += $instalationEndTime->diffInMinutes($instalationStartTime);
                }

                $totalAvailableTime += $endTime->diffInMinutes($startTime) - $totalInstalationTime;
            }

            // 4. Seleccionar Técnico con Más Tiempo Disponible
            if ($totalAvailableTime > $maxAvailableTime) {
                $maxAvailableTime = $totalAvailableTime;
                $availableTechnician = $technician;
            }
        }

        return $availableTechnician;
    }
}
