<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CallActivity;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\LeadObservation;
use App\Models\SaleActivity;
use App\Models\User;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{

    public function getCurrentLead(Request $request)
    {
        $user = $request->user();

        $lead = $user->leadAssignments()->latest('order')->where('active', true)->first();

        if (!$lead) {
            self::assignNextLead($user);
        }

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.saleActivities.user', 'lead.user', 'saleActivities.user')->first();

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getNextLead(Request $request)
    {
        $user = $request->user();

        self::assignNextLead($user);

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.saleActivities.user', 'lead.user', 'saleActivities.user')->first();

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
        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.saleActivities.user', 'lead.user', 'saleActivities.user')->first();

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

            if (!$data['nextLeadId']) {
                return null;
            }
            // Crear la nueva asignación
            $nextAssignment = new LeadAssignment([
                'lead_id' => $data['nextLeadId'],
                'round' => $data['round'],
                'order' => $nextOrder,
                'active' => true, // El lead se marca como activo por defecto
                'project_id' => $data['project_id'],
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

    private function findNextLeadId($user)
    {
        // Obtener todos los leads ya asignados al usuario
        $activeAssignedLeadIds = LeadAssignment::where('active', true)->pluck('lead_id');

        // Get max activeAssignedLeadIds
        $round = LeadAssignment::max('round') ?? 1;
        $projects = $user->projects_pivot->pluck('lead_project_id')->toArray();
        $maxActiveAssignedLeadId = LeadAssignment::where('round', $round)
        ->whereIn('project_id', $projects)
        ->when(in_array(null, $projects), function ($query) {
            return $query->orWhereNull('project_id');
        })
        ->max('lead_id') ?? 0;

        // Encuentra el próximo lead no asignado al usuario


        if (count($projects) == 0) {
            return [
                'round'      => $round,
                'project_id' => null,
                'nextLeadId' => null
            ];
        }

        $nextLead = Lead::whereIn('lead_project_id', $projects)
            ->when(in_array(null, $projects), function ($query) {
                return $query->orWhereNull('lead_project_id');
            })
            ->where('id', '>', $maxActiveAssignedLeadId)
            ->where('status', 'Nuevo')
            ->orderBy('id', 'ASC')
            ->first();


        if (!$nextLead) {
            $round++;
            $nextLead = $this->startNewRound($projects);
        }

        return [
            'round' => $round,
            'nextLeadId' => $nextLead->id,
            'project_id' => $nextLead->lead_project_id
        ];
    }

    private function startNewRound($projects)
    {
        // Comienza una nueva vuelta asignando el primer lead disponible
        $lead = Lead::orderBy('id')
            ->where('status', 'Nuevo')
            ->whereIn('lead_project_id', $projects)
            ->when(in_array(null, $projects), function ($query) {
                return $query->orWhereNull('lead_project_id');
            })
            ->first();

        return $lead;
    }


    public function saveBasicData(Request $request, $id)
    {
        $user_id = null;

        $user = $request->user();
        if ($user->role_id == 1) {
            $user_id = $request->user_id;
        }

        if ($user->role_id != 1) {
            if ($request->status == 'Interesado') {
                $user_id = $user->id;
            }
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
        if ($request->date) {
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
            return $q->where(function ($q) use ($searchString) {
                return $q->where('name', 'LIKE', "%$searchString%")
                    ->orWhere('courses', 'LIKE', "%$searchString%")
                    ->orWhere('status', 'LIKE', "%$searchString%")
                    ->orWhere('origin', 'LIKE', "%$searchString%")
                    ->orWhere('phone', 'LIKE', "%$searchString%")
                    ->orWhere('email', 'LIKE', "%$searchString%")
                    ->orWhere('document', 'LIKE', "%$searchString%");
            });
        })->when($request->project_id, function ($query) use ($request) {
            $p = $request->project_id == 'Base' ? null : $request->project_id;
            return $query->where('lead_project_id', $p);
        })->with(['observations' => function ($query) {
            return $query->where('schedule_call_datetime', '<>', NULL)->orderBy('schedule_call_datetime', 'DESC');
        }])->with('user', 'leadProject')
        ->where('status', '<>', 'Archivado')
        ->orderBy('id', 'DESC')
        ->paginate($perPage);

        return ApiResponseController::response("Exito", 200, $leads);
    }


    public function getLead(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('id', $id)
            ->when($user->role->name != 'Administrador', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->with('user', 'saleActivities.user')
            ->first();

        return ApiResponseController::response("Exito", 200, $lead);
    }

    public function archiveLead(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('id', $id)
            ->when($user->role->name != 'Administrador', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->update([
                'status' => 'Archivado'
            ]);

        return ApiResponseController::response("Exito", 200, $lead);
    }

    public function getNextScheduleCall(Request $request)
    {
        $user = $request->user();

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $lead = Lead::where('user_id', $user->id)
            ->where('status', 'Interesado')
            ->whereHas('saleActivities', function ($query) use ($now) {
                $now = Carbon::now()->format('Y-m-d H:i:s');
                return $query->where('schedule_call_datetime', '<>', NULL)
                    ->where('schedule_call_datetime', '>', $now)
                    ->orderBy('schedule_call_datetime', 'ASC');
            })
            ->with(['saleActivities' => function($q){
                return $q->where('schedule_call_datetime', 'IS NOT', NULL)
                ->where('schedule_call_datetime', '<>', NULL);
            }])
            ->first();

        return ApiResponseController::response("Exito", 200, $lead);
    }


    public function getLeadsAssignments(Request $request)
    {


        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $leadAssignament = LeadAssignment::with('user', 'lead', 'observations', 'saleActivities')
            ->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('user_id', $request->user()->id);
            })
            ->when($request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->when($request->start && $request->end, function ($query) use ($request) {
                $start = Carbon::parse($request->start)->startOfDay();
                $end = Carbon::parse($request->end)->endOfDay();
                return $query->whereBetween('assigned_at', [$start, $end]);
            })
            ->withCount('calls')
            ->orderBy('assigned_at', 'DESC')
            ->paginate($perPage);

            // attach duration to each sale activity
            $leadAssignament->getCollection()->transform(function ($leadAssignment) {
                // Añadimos el accesorio al modelo
                $leadAssignment->saleActivities->each(function ($saleActivity) {
                    $saleActivity->append('duration');
                    return $saleActivity;
                });
                return $leadAssignment;
            });

        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }

    public function getAssignmentsByHour(Request $request)
    {
        $user = $request->user();
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $roleAsesorId = 2; // ID del rol de asesor

        // Obtener todos los asesores
        $asesores = User::where('role_id', $roleAsesorId)
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('user_id'));
            })->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('id', $request->user()->id);
            })
            ->with('projects_pivot')
            ->get();


        $reporte = $asesores->map(function ($asesor) use ($start, $end) {
            // Obtener los lead assignments del asesor, agrupados por día y hora
            $assignmentsPorHora = LeadAssignment::where('user_id', $asesor->id)
                ->whereBetween('assigned_at', [$start->startOfDay()->format('Y-m-d H:i:s'), $end->endOfDay()->format('Y-m-d H:i:s')])
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
            $count = 0;
            foreach($assignmentsPorHora as $key => $value){
                foreach($value as $key2 => $value2){
                    $count += $value2->value;
                }
            }

            return [
                'id'             => $asesor->id,
                'name'           => $asesor->name,
                'email'          => $asesor->email,
                'active_working' => $asesor->active_working,
                'role_id'        => $asesor->role_id,
                'count'          => $count,
                'projects_pivot' => $asesor->projects_pivot,
                'data'           => $datos,
                'datica'         => $assignmentsPorHora
            ];
        });

        return ApiResponseController::response("Exito", 200, $reporte);
    }

    public function updateSalesActivity(Request $request)
    {
        if ($request->end) {
            $callActivity = SaleActivity::where('user_id', $request->user_id)
                ->where('lead_id', $request->lead_id)
                ->where('end', null)
                ->where('type', $request->type)
                ->where('lead_assignment_id', $request->lead_assignment_id)
                ->first();

            $end = Carbon::now();

            $schedule_call_datetime = null;
            if ($request->schedule_call_datetime) {
                $schedule_call_datetime = Carbon::parse($request->schedule_call_datetime);
            }

            $callActivity->update([
                'end' => $end,
                'answered' => $request->answered,
                'observation' => $request->observation,
                'schedule_call_datetime' => $schedule_call_datetime
            ]);

            return ApiResponseController::response("Exito", 200, $callActivity);
        }

        $start = Carbon::now();

        $saleActivity = SaleActivity::create([
            'user_id'            => $request->user_id,
            'lead_id'            => $request->lead_id,
            'lead_assignment_id' => $request->lead_assignment_id,
            'type'               => $request->type,
            'start'              => $start
        ]);

        return ApiResponseController::response("Exito", 200, $saleActivity);
    }

    function diconnectCallActivity(Request $request)
    {
        if (!(is_array($request->events) && isset($request->events[0]) && $request->events[0]['name'] === 'channel_vacated')) {
            return ApiResponseController::response("Exito", 200, []);
        }

        $user_id = explode('.', $request->events[0]['channel'])[1];

        $callActivity = SaleActivity::where('user_id', $user_id)
            ->where('end', null)
            ->where('lead_assignment_id', $request->lead_assignment_id)
            ->get();

        if ($callActivity->count() > 0) {
            $end = Carbon::now();

            $callActivity->each(function ($item) use ($end) {
                $item->update([
                    'end' => $end
                ]);
            });
        }
        return ApiResponseController::response("Exito", 201, []);
    }

    function getLastCallActivity(Request $request)
    {
        $user = $request->user();

        $sale = SaleActivity::where('user_id', $user->id)
            ->orderBy('end', 'DESC')
            ->first();

        return ApiResponseController::response("Exito", 200, $sale);
    }

    public function getCalls(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $calls = SaleActivity::where('type', 'Llamada')
            ->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('user_id', $request->user()->id);
            })
            ->when($request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->with('lead')
            ->orderBy('created_at', 'DESC')
            ->paginate($perPage);

        $calls->getCollection()->transform(function ($call) {
            // Añadimos el accesorio al modelo
            return $call->append('duration');
        });

        return ApiResponseController::response("Exito", 200, $calls);
    }

    public function getCallsByHour(Request $request)
    {
        $user = $request->user();
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }

        $roleAsesorId = 2; // ID del rol de asesor

        // Obtener todos los asesores
        $asesores = User::where('role_id', $roleAsesorId)
            ->when($request->input('user_id'), function ($query) use ($request) {
                return $query->where('id', $request->input('user_id'));
            })->when($user->role_id != 1, function ($query) use ($request) {
                return $query->where('id', $request->user()->id);
            })
            ->with('projects_pivot')
            ->get();


        $reporte = $asesores->map(function ($asesor) use ($start, $end) {
            $count = 0;

            $calls = SaleActivity::where('user_id', $asesor->id)
                ->where('type', 'Llamada')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as fecha, HOUR(created_at) as hora, COUNT(*) as value')
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
                    $value = $calls[$fechaFormato][$horaKey]->value ?? 0;
                    $count += $value;

                    $datos[] = [
                        'datetime' => $fechaFormato . ' ' . $horaKey . ':00:00',
                        'value' => $value
                    ];
                }
            }

            return [
                'id'             => $asesor->id,
                'name'           => $asesor->name,
                'email'          => $asesor->email,
                'active_working' => $asesor->active_working,
                'role_id'        => $asesor->role_id,
                'count'          => $count,
                'projects_pivot' => $asesor->projects_pivot,
                'data'           => $datos
            ];
        });

        return ApiResponseController::response("Exito", 200, $reporte);
    }


    public function getMainStats(Request $request)
    {
        $user = $request->user();
        $start = Carbon::now()->startOfDay();
        $end = Carbon::now()->endOfDay();
        if ($request->start) {
            $start = Carbon::parse($request->input('start'));
            $end = Carbon::parse($request->input('end'))->endOfDay();
        }


        $activities = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->where('type', 'Llamada')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalSeconds = 0;

        foreach ($activities as $activity) {
            $_start = Carbon::parse($activity->start);
            $_end = Carbon::parse($activity->end);

            $totalSeconds += $_end->diffInSeconds($_start);
        }

        $hours = $totalSeconds / 3600;
        $hours = number_format($hours, 2, '.', '');
        // Convert to float
        $hours = floatval($hours);


        $leadCounts = LeadAssignment::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id == 1, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->whereBetween('assigned_at', [$start, $end])
            ->count();

        $callsCount = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
            return $query->where('user_id', $request->user()->id);
        })
            ->when($user->role_id, function ($query) use ($request) {
                $query->when($request->user_id, function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                });
            })
            ->where('type', 'Llamada')
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $data = [
            'hoursInCall' => $hours,
            'leadCounts' => $leadCounts,
            'callsCount' => $callsCount
        ];
        return ApiResponseController::response("Exito", 200, $data);
    }
}
