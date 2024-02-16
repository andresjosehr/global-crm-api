<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SapInstalation;
use App\Models\StaffAvailabilitySlot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UsersController extends Controller
{



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

        return ApiResponseController::response('Consulta Exitosa', 200, $availableTechnician);
    }

    public function getSellsUsers()
    {
        $users = User::where('role_id', 2)
            ->where('active', 1)
            ->get();

        return ApiResponseController::response('Consulta Exitosa', 200, $users);
    }

    public function toggleStatus(Request $request)
    {
        $user = $request->user();

        $user->active_working = !$user->active_working;
        $user->save();

        return ApiResponseController::response('Consulta Exitosa', 200, $user);
    }
}
