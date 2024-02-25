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

    public function getList(Request $request)
    {

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $users = User::with('role')
            ->withCount('studentsAssigned')
            ->when($request->input('role_id'), function ($query) use ($request) {
                return $query->where('role_id', $request->input('role_id'));
            })
            ->when(gettype($request->input('status')) == 'string', function ($query) use ($request) {
                return $query->where('active', $request->input('status'));
            })

            ->paginate($perPage);

        // return $users->items();
        $users->data = $this->getUserWithCount(collect($users->items()));

        return ApiResponseController::response('Consulta Exitosa', 200, $users);
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 401);
        }

        $user = User::find($id);

        $user->active = !$user->active;
        $user->save();

        return ApiResponseController::response('Consulta Exitosa', 200, $user);
    }

    public function save(Request $request)
    {
        $user = $request->user();
        if ($user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 401);
        }

        $user = new User();

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role_id = $request->input('role_id');
        $user->photo = $request->input('photo');
        $user->zadarma_id = $request->input('zadarma_id');
        $user->active = $request->input('active');
        $user->save();

        return ApiResponseController::response('Usuario creado exitosamente', 200, $user);
    }


    public function update(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 401);
        }

        $user = User::find($id);

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->password = bcrypt($request->input('password'));
        $user->role_id = $request->input('role_id');
        $user->photo = $request->input('photo');
        $user->zadarma_id = $request->input('zadarma_id');
        $user->active = $request->input('active');
        $user->save();

        return ApiResponseController::response('Usuario actualizado exitosamente', 200, $user);
    }

    public function get(Request $request, $id)
    {

        if ($request->user()->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 401);
        }

        $user = User::with('role')->find($id);

        return ApiResponseController::response('Consulta Exitosa', 200, $user);
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

        return ApiResponseController::response('Consulta Exitosa', 200, $availableTechnician);
    }

    public function getSellsUsers()
    {
        $users = User::where('role_id', 2)
            ->where('active', 1)
            ->get();

        return ApiResponseController::response('Consulta Exitosa', 200, $users);
    }

    public function toggleWorkingStatus(Request $request)
    {
        $user = $request->user();

        $user->active_working = !$user->active_working;
        $user->save();

        return ApiResponseController::response('Consulta Exitosa', 200, $user);
    }

    public function getUserWithCount($users, $date = null)
    {
        return $users->map(function ($user) use ($date) {


            $user->students_assigned_date_count = $user->students->filter(function ($student) use ($date) {
                if (!$date) {
                    return true;
                }
                if ($student->orders->count() > 0) {
                    return $student->orders[0]->orderCourses[0]->start == $date;
                }
                return false;
            })->count();
            $user->date = $date;
            unset($user->students);


            return $user;
        })
            ->values();
    }
}
