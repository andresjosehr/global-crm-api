<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadObservation;
use App\Models\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;

class LeadsController extends Controller
{

    public function getCurrentLead(Request $request)
    {
        $user = $request->user();

        $lead = $user->leadAssignments()->latest('order')->where('active', true)->first();


        if (!$lead) {
            self::assignNextLead($user);
        }

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();
        // ATTATCH observations.user



        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getNextLead(Request $request)
    {
        $user = $request->user();

        self::assignNextLead($user);

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getPreviousLead(Request $request)
    {
        $user = $request->user();

        $previousAssignment = self::assignPreviousLead($user);

        if (!$previousAssignment) {
            return ApiResponseController::response("No hay leads previos", 200);
        }

        $lead = $previousAssignment->lead;
        // $lead = Lead::with('observations.user')->find($lead->id);
        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.observations.user', 'observations.user')->first();

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function assignNextLead(User $user)
    {
        // Obtener el último lead asignado al asesor y el orden actual
        $lastAssignment = $user->leadAssignments()->latest('order')->where('active', true)->first();
        $nextOrder = $lastAssignment ? $lastAssignment->order + 1 : 1;

        // Verificar si ya existe un lead asignado con el siguiente orden
        $nextAssignment = $user->leadAssignments()->where('order', $nextOrder)->first();

        if ($nextAssignment) {
            // Reactivar el lead siguiente si existe
            $nextAssignment->update(['active' => true]);
        } else {
            // Si no existe, determinar el próximo lead a asignar
            $data = $this->findNextLeadId($user, $lastAssignment);

            // Crear la nueva asignación
            $nextAssignment = new LeadAssignment([
                'lead_id' => $data['nextLeadId'],
                'round' => $data['round'],
                'order' => $nextOrder,
                'active' => true, // El lead se marca como activo por defecto
                'assigned_at' => now(),
            ]);

            $user->leadAssignments()->save($nextAssignment);
        }

        // Marcar el lead anterior como inactivo
        if ($lastAssignment) {
            $lastAssignment->update(['active' => false]);
        }

        return $nextAssignment;
    }

    public function assignPreviousLead(User $user)
    {
        // Obtener el lead actualmente asignado y disminuir el orden
        $currentAssignment = $user->leadAssignments()->latest('order')->where('active', true)->first();

        if (!$currentAssignment || $currentAssignment->order <= 1) {
            // No hay lead previo o es el primer lead
            return null;
        }

        $previousOrder = $currentAssignment->order - 1;
        $previousAssignment = $user->leadAssignments()->where('order', $previousOrder)->first();

        // Marcar el lead actual como inactivo y el anterior como activo
        if ($previousAssignment) {
            $currentAssignment->update(['active' => false]);
            $previousAssignment->update(['active' => true]);
        }

        return $previousAssignment;
    }

    private function findNextLeadId()
    {
        // Obtener todos los leads ya asignados al usuario
        $activeAssignedLeadIds = LeadAssignment::where('active', true)->pluck('lead_id');

        // Get max activeAssignedLeadIds
        $round = LeadAssignment::max('round') ?? 1;
        $maxActiveAssignedLeadId = LeadAssignment::where('round', $round)->max('lead_id') ?? 0;

        // Encuentra el próximo lead no asignado al usuario
        $nextLeadId = Lead::where('id', '>', $maxActiveAssignedLeadId)
            ->where('status', 'Nuevo')
            ->orderBy('id', 'ASC')
            ->first();

        if (!$nextLeadId) {
            $round++;
            $nextLeadId = $this->startNewRound();
        } else {
            $nextLeadId = $nextLeadId->id;
        }

        return [
            'round' => $round,
            'nextLeadId' => $nextLeadId
        ];
    }

    private function startNewRound()
    {
        // Comienza una nueva vuelta asignando el primer lead disponible
        return Lead::orderBy('id')->where('status', 'Nuevo')->first()->id;
    }


    public function saveBasicData(Request $request, $id)
    {
        $user_id = null;
        if ($request->status == 'Interesado' ||  $request->status == 'No Interesado') {
            $user_id = $request->user()->id;
        }
        $lead = Lead::where('id', $id)->update([
            'name' => $request->name,
            'courses' => $request->courses,
            'phone' => $request->phone,
            'status' => $request->status,
            'email' => $request->email,
            'origin' => $request->origin,
            'document' => $request->document,
            'user_id' => $user_id

        ]);

        $lead = Lead::with('observations.user')->find($id);

        return ApiResponseController::response("Exito", 200, $lead);
    }

    public function saveObservation(Request $request, $leadId, $leadAssignamentId = null)
    {
        // Get current lead assignment

        $schedule_call_datetime = null;
        if ($request->date && $request->time) {
            $date = Carbon::parse($request->date);
            $time = Carbon::parse($request->time);
            $schedule_call_datetime = $date->format('Y-m-d') . ' ' . $time->format('H:i:s');

            Lead::where('id', $leadId)->update([
                'status' => 'Interesado',
                'user_id' => $request->user()->id,
            ]);
        }

        $leadObservation = LeadObservation::create([
            'user_id'                => $request->user()->id,
            'lead_id'                => $leadId,
            'call_status'            => $request->call_status,
            'observation'            => $request->observation,
            'lead_assignment_id'     => $leadAssignamentId,
            'schedule_call_datetime' => $schedule_call_datetime
        ]);

        $leadObservation = LeadObservation::with('user')->find($leadObservation->id);



        return ApiResponseController::response("Exito", 200, $leadObservation);
    }

    public function getLeads(Request $request, $mode)
    {
        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;

        $searchString = $request->input('searchString') ? $request->input('searchString') : '';
        $searchString = $request->input('searchString') != 'null' ? $request->input('searchString') : '';

        $leads = Lead::when($mode == 'potenciales', function ($query) use ($user) {
            return $query->where('user_id', $user->id);
        })->when($searchString, function ($q) use ($searchString) {
            $q->where('name', 'LIKE', "%$searchString%")
                ->orWhere('courses', 'LIKE', "%$searchString%")
                ->orWhere('status', 'LIKE', "%$searchString%")
                ->orWhere('origin', 'LIKE', "%$searchString%")
                ->orWhere('phone', 'LIKE', "%$searchString%")
                ->orWhere('email', 'LIKE', "%$searchString%")
                ->orWhere('document', 'LIKE', "%$searchString%");
        })
            ->with(['observations' => function ($query) {
                return $query->where('schedule_call_datetime', '<>', NULL)->orderBy('schedule_call_datetime', 'DESC');
            }])->with('user')->paginate($perPage);

        return ApiResponseController::response("Exito", 200, $leads);
    }


    public function getLead(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('id', $id)
            ->when($user->role->name != 'Administrador', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->with('observations.user', 'user')
            ->first();

        return ApiResponseController::response("Exito", 200, $lead);
    }

    public function getNextScheduleCall(Request $request)
    {
        $user = $request->user();

        $lead = Lead::where('user_id', $user->id)
            ->where('status', 'Interesado')
            ->whereHas('observations', function ($query) {
                $now = Carbon::now()->format('Y-m-d H:i:s');
                return $query->where('schedule_call_datetime', '<>', NULL)
                    ->where('schedule_call_datetime', '>', $now)
                    ->orderBy('schedule_call_datetime', 'ASC');
            })->with(['observations' => function ($query) {
                $now = Carbon::now()->format('Y-m-d H:i:s');
                return $query->where('schedule_call_datetime', '<>', NULL)
                    ->where('schedule_call_datetime', '>', $now)
                    ->orderBy('schedule_call_datetime', 'ASC');
            }])
            ->first();

        return ApiResponseController::response("Exito", 200, $lead);
    }


    public function getActivityHistory(Request $request)
    {

        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $leadAssignament = LeadAssignment::with('user', 'lead', 'observations')->paginate($perPage);

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getActivityHistoryByUser(Request $request)
    {
        $start = Carbon::parse($request->input('start'));
        // Ajustar la fecha de fin para incluir el final del día
        $end = Carbon::parse($request->input('end'))->endOfDay();

        $roleAsesorId = 2; // ID del rol de asesor

        // Obtener todos los asesores
        $asesores = User::where('role_id', $roleAsesorId)->get();

        $reporte = $asesores->map(function ($asesor) use ($start, $end) {
            // Obtener los lead assignments del asesor, agrupados por día y hora
            $assignmentsPorHora = LeadAssignment::where('user_id', $asesor->id)
                ->whereBetween('assigned_at', [$start, $end])
                ->selectRaw('DATE(assigned_at) as fecha, HOUR(assigned_at) as hora, COUNT(*) as value')
                ->groupBy('fecha', 'hora')
                ->get()
                ->groupBy('fecha')
                ->mapWithKeys(function ($item) {
                    return [$item[0]->fecha => $item->keyBy('hora')];
                });

            $datos = [];

            // Iterar sobre cada día en el rango
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                $fechaFormato = $date->format('Y-m-d');
                foreach (range(0, 23) as $hora) {
                    $horaKey = str_pad($hora, 2, '0', STR_PAD_LEFT);

                    // Verificar si existen datos para la fecha y hora específicas
                    $value = $assignmentsPorHora[$fechaFormato][$horaKey]->value ?? 0;

                    $datos[] = [
                        'datetime' => $fechaFormato . ' ' . $horaKey . ':00:00',
                        'value' => $value
                    ];
                }
            }

            return [
                'name'    => $asesor->name,
                'email'   => $asesor->email,
                'role_id' => $asesor->role_id,
                'count'   => $assignmentsPorHora->sum('value'),
                'data'    => $datos
            ];
        });

        return ApiResponseController::response("Exito", 200, $reporte);
    }
}
