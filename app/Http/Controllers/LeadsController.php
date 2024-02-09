<?php

namespace App\Http\Controllers;

use DatePeriod;
use DateInterval;
use Carbon\Carbon;
use App\Models\Lead;
use App\Models\User;
use App\Models\Order;
use App\Models\Student;
use App\Models\CallActivity;
use App\Models\SaleActivity;
use Illuminate\Http\Request;
use App\Models\LeadAssignment;
use App\Models\LeadObservation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class LeadsController extends Controller
{

    public function getCurrentLead(Request $request)
    {
        $user = $request->user();

        $lead = $user->leadAssignments()->latest('order')->where('active', true)->first();

        if (!$lead) {
            self::assignNextLead($user);
        }

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)
            ->with('lead.user')
            ->with(['lead.saleActivities' => function ($query) use ($user) {
                return $query->with('user')->orderBy('id', 'DESC');
            }])
            ->with(['saleActivities.user' => function ($query) use ($user) {
                return $query->orderBy('id', 'DESC');
            }])
            ->with('lead.student')
            ->first();

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


        // Obtener la ronda actual de asignaciones
        $round = LeadAssignment::max('round') ?? 1;

        // Obtener todos los leads ya asignados al usuario
        $assignedLeadsIds = LeadAssignment::where('round', $round)->pluck('lead_id');

        // Obtener los IDs de los proyectos asignados al usuario
        $projects = $user->projects_pivot->pluck('lead_project_id')->toArray();

        // Verificar si el usuario no tiene proyectos asignados
        if (count($projects) == 0) {
            return [
                'round'      => $round,
                'project_id' => null,
                'nextLeadId' => null
            ];
        }

        // Intentar encontrar el lead más reciente no asignado al usuario
        $nextLead = Lead::whereIn('lead_project_id', $projects)
            ->when(in_array(null, $projects), function ($query) {
                return $query->orWhereNull('lead_project_id');
            })
            ->whereNotIn('id', $assignedLeadsIds)
            ->where('status', 'Nuevo')
            ->orderBy('created_at', 'DESC') // Ordenar por fecha de creación, no por ID
            ->first();

        // Si no hay leads nuevos, iniciar una nueva ronda
        if (!$nextLead) {
            $round++;
            $nextLead = $this->startNewRound($projects);
        }

        // Devolver el ID del próximo lead a asignar, la ronda y el ID del proyecto
        return [
            'round' => $round,
            'nextLeadId' => $nextLead ? $nextLead->id : null,
            'project_id' => $nextLead ? $nextLead->lead_project_id : null
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
            'name'             => $request->name,
            'courses'          => $request->courses,
            'phone'            => $request->phone,
            'status'           => $request->status,
            'email'            => $request->email,
            'origin'           => $request->origin,
            'document'         => $request->document,
            'user_id'          => $user_id,
            'country_id'       => $request->country_id,
            // 'city_id'          => $request->city_id,

            'document_type_id' => $request->document_type_id,

        ]);

        $lead = Lead::with('observations.user')->with('student')->find($id);

        if ($lead->student) {
            $student = Student::where('id', $lead->student->id)->update([
                'name'             => $request->name,
                'email'            => $request->email,
                'phone'            => $request->phone,
                'document'         => $request->document,
                'country_id'       => $request->country_id,
                // 'city_id'          => $request->city_id,
                // 'state_id'         => $request->state_id,
                'document_type_id' => $request->document_type_id,
            ]);
        }

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
                return $query->where('user_id', $user->id)
                    ->where('status', '<>', 'Archivado')
                    ->where('status', '<>', 'Nuevo');
            })
                ->when($mode == 'potenciales', function ($q) use ($user) {
                    return $q->where('user_id', $user->id)->where(function ($q2) {
                        return $q2->where('status', 'Potencial')->orWhere('status', 'Interesado');
                    });
                })
                ->when($mode == 'matriculados', function ($q) use ($user) {
                    return $q->where('user_id', $user->id)
                        ->where('status', 'Matriculado');
                })
                ->when($mode == 'base-general', function ($q) use ($user) {
                    return $q;
                })
                ->when($searchString, function ($q) use ($searchString) {
                    return $q->where(function ($q) use ($searchString) {
                        return $q->where('name', 'LIKE', "%$searchString%")
                            ->orWhere('courses', 'LIKE', "%$searchString%")
                            ->orWhere('status', 'LIKE', "%$searchString%")
                            ->orWhere('origin', 'LIKE', "%$searchString%")
                            ->orWhere('phone', 'LIKE', "%$searchString%")
                            ->orWhere('email', 'LIKE', "%$searchString%")
                            ->orWhere('document', 'LIKE', "%$searchString%");
                    });
                })
                ->when($request->project_id, function ($query) use ($request) {
                    if ($request->project_id != 'Todos') {
                        $p = $request->project_id == 'Base' ? null : $request->project_id;
                        return $query->where('lead_project_id', $p);
                    }

                    // Get all projects
                    $projects = $request->user()->projects_pivot->pluck('lead_project_id')->toArray();
                    // attach null to projects
                    // $projects[] = null;
                    return $query->whereIn('lead_project_id', $projects)->orWhereNull('lead_project_id');
                })
                ->when(!$request->project_id, function ($query) use ($request) {
                    return $query->where('lead_project_id', "Base");
                })
                ->when($request->automatic_import === 'true', function ($query) use ($request) {
                    return $query->where('channel_id', '<>', NULL);
                })
                ->when($request->numbers, function ($query) use ($request) {
                    return $query->whereIn('phone', explode(',', $request->numbers));
                })
                ->with(['observations' => function ($query) {
                    return $query->where('schedule_call_datetime', '<>', NULL)->orderBy('schedule_call_datetime', 'DESC');
                }])->with('user', 'leadProject', 'saleActivities.user')
                ->orderBy('id', 'DESC')
                ->paginate($perPage);

            $leads->getCollection()->transform(function ($leadAssignment) {
                // Añadimos el accesorio al modelo
                $leadAssignment->saleActivities->each(function ($saleActivity) {
                    $saleActivity->append('duration');
                    return $saleActivity;
                });
                return $leadAssignment;
            });
            return ApiResponseController::response("Exito", 200, $leads);


    }


    public function getLead(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('id', $id)
            ->when($user->role->name != 'Administrador', function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->with('student', 'user', 'saleActivities.user')
            ->first();

        return ApiResponseController::response("Exito", 200, $lead);
    }


    public function getLeadByPhone(Request $request, $phone)
    {
        $user = $request->user();

        $lead = Lead::where('phone', $phone)

            ->with('student', 'user', 'saleActivities.user')
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


    public function archiveLeadByBatch(Request $request,)
    {
        $user = $request->user();

        // Check if administator
        if ($user->role->name != 'Administrador') {
            return ApiResponseController::response("No tienes permisos para realizar esta acción", 403);
        }

        $lead = Lead::whereIn('id', $request->ids)
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
            ->with(['saleActivities' => function ($q) {
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
            ->where('active', true)
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
                foreach (range(8, 23) as $hora) {
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
            foreach ($assignmentsPorHora as $key => $value) {
                foreach ($value as $key2 => $value2) {
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

            if (!$callActivity) {
                return ApiResponseController::response("Exito", 200, []);
            }

            $end = Carbon::now();

            $schedule_call_datetime = null;
            if ($request->schedule_call_datetime) {
                $schedule_call_datetime = Carbon::parse($request->schedule_call_datetime);
                // Update lead status as "Interesado"
                Lead::where('id', $request->lead_id)->update([
                    'status' => 'Interesado',
                    'user_id' => $request->user()->id,
                ]);
            } else {
                // Update lead status as "No interesado"
                Lead::where('id', $request->lead_id)->update([
                    'status' => $request->lead_status,
                ]);
            }


            $callActivity->update([
                'end' => $end,
                'answered' => $request->answered,
                'observation' => $request->observation,
                'schedule_call_datetime' => $schedule_call_datetime
            ]);

            $callActivity = SaleActivity::where('id', $callActivity->id)->with('user')->first();

            return ApiResponseController::response("Exito", 200, $callActivity);
        }

        $start = Carbon::now()->format('Y-m-d H:i:s');

        $saleActivityExists = SaleActivity::where('user_id', $request->user_id)
            ->where('lead_id', $request->lead_id)
            ->where('end', null)
            ->where('type', $request->type)
            ->where('lead_assignment_id', $request->lead_assignment_id)
            ->first();

        $saleActivity = null;
        if (!$saleActivityExists) {
            $saleActivity = SaleActivity::create([
                'user_id'            => $request->user_id,
                'lead_id'            => $request->lead_id,
                'lead_assignment_id' => $request->lead_assignment_id,
                'type'               => $request->type,
                'start'              => $start
            ]);
        }

        $activity = self::getLastCallActivity($request, true);

        return ApiResponseController::response("Exito", 200, $activity);
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

    function getLastCallActivity(Request $request, $self = false)
    {
        $user = $request->user();

        $sale = SaleActivity::where('user_id', $user->id)
            ->with('user')
            ->orderBy('end', 'DESC')
            ->first();


        return $self ? $sale : ApiResponseController::response("Exito", 200, $sale);
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
    if($request->user_id){
        $user = User::find($request->user_id);
    }else{
        $user = $request->user();
    }
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

    $answeredCallsCount = SaleActivity::when($user->role_id != 1, function ($query) use ($request) {
        return $query->where('user_id', $request->user()->id);
    })
        ->when($user->role_id, function ($query) use ($request) {
            $query->when($request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            });
        })
        ->where('type', 'Llamada')
        ->where('answered', 1) // Filtrar solo las llamadas contestadas
        ->whereBetween('created_at', [$start, $end])
        ->count();

        $callStats = $this->getCallStats($user);

        $minutesStats = $this->minutesStats($user, $start, $end);

        $salesStats = $this->getSalesStats($user);

        $categoriesStats = $this->getSalesCategoriesStats($user);

    $data = [
        'hoursInCall' => $hours,
        'leadCounts' => $leadCounts,
        'callsCount' => $callsCount,
        'answeredCallsCount' => $answeredCallsCount,
        'categoriesSales' =>  $categoriesStats,
        'salesStats' => $salesStats,
        'minutesStats' => $minutesStats,
        'callsStats' => $callStats,
        'user_id' => $user->id
    ];



    return ApiResponseController::response("Exito", 200, $data);
}

public function getSalesCategoriesStats($user)
{
    return DB::table('orders')
        ->selectRaw('
            COUNT(orders.id) AS total_orders,
            CAST(COALESCE(SUM(CASE WHEN order_courses_count = 1 THEN 1 ELSE 0 END), 0) AS UNSIGNED) AS plus_sales,
            CAST(COALESCE(SUM(CASE WHEN order_courses_count = 2 THEN 1 ELSE 0 END), 0) AS UNSIGNED) AS premium_sales,
            CAST(COALESCE(SUM(CASE WHEN order_courses_count = 5 THEN 1 ELSE 0 END), 0) AS UNSIGNED) AS platinum_sales
        ')
        ->leftJoin(DB::raw('(SELECT order_id, COUNT(*) AS order_courses_count FROM order_courses GROUP BY order_id) AS oc_counts'), 'orders.id', '=', 'oc_counts.order_id')
        ->whereYear('orders.created_at', Carbon::now()->year)
        ->whereMonth('orders.created_at', Carbon::now()->month)
        ->where('orders.created_by', $user->id)
        ->first();
}



public function getSalesStats($user)
{
    // Ventas en el mes actual
    $totalSalesThisMonth = Order::where('created_by', $user->id)
        ->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year)
        ->count();

    // Mes con mayor venta y cantidad de ventas mensuales
    $monthlySales = Order::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total_sales')
        ->where('created_by', $user->id)
        ->groupBy('year', 'month')
        ->orderByDesc('total_sales')
        ->first();

    $bestMonth = null;
    $bestMonthSales = 0;
    if ($monthlySales) {
        $bestMonth = Carbon::create($monthlySales->year, $monthlySales->month)->format('F');
        $bestMonthSales = $monthlySales->total_sales;
    }

     $totalMonthlySales = Order::where('created_by', $user->id)
        ->selectRaw('COUNT(*) as total_sales')
        ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
        ->pluck('total_sales')
        ->sum();

    // Media de ventas mensuales
    $averageMonthlySales = $totalMonthlySales / Carbon::now()->month;

    $initialObjective = 40;
    $maxObjective = 60;
    $objectiveCalls = $initialObjective;

// Verificar si se ha alcanzado el objetivo actual y no hemos alcanzado el máximo
    if ($totalSalesThisMonth >= $objectiveCalls && $objectiveCalls < $maxObjective) {
    // Incrementar el objetivo para el próximo nivel, pero asegúrate de no pasar del máximo
    $objectiveCalls += 10;
    if ($objectiveCalls > $maxObjective) {
        $objectiveCalls = $maxObjective;
    }
}

    return [
        'objetiveCalls' => $objectiveCalls,
        'totalSalesThisMonth' => $totalSalesThisMonth,
        'bestMonth' => $bestMonth,
        'bestMonthSales' => $bestMonthSales,
        'averageMonthlySales' => (float) number_format($averageMonthlySales, 2, '.', ''),
    ];
}


public function minutesStats($user, $start, $end)
{
    $totalMinutesToday = SaleActivity::where('user_id', $user->id)
        ->where('type', 'Llamada')
        ->whereDate('created_at', Carbon::today())
        ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(end, start)) / 60'));

    $firstDayLastMonth = Carbon::now()->subMonth()->startOfMonth();
    $lastDayLastMonth = Carbon::now()->subMonth()->endOfMonth();
    $maxMinutesLastMonth = SaleActivity::selectRaw('MAX(total_minutes) as max_minutes')
        ->from(function ($query) use ($user, $firstDayLastMonth, $lastDayLastMonth) {
            $query->selectRaw('SUM(TIME_TO_SEC(TIMEDIFF(end, start)) / 60) as total_minutes')
                ->from('sales_activities')
                ->where('user_id', $user->id)
                ->where('type', 'Llamada')
                ->whereBetween('created_at', [$firstDayLastMonth, $lastDayLastMonth])
                ->groupBy(DB::raw('DATE(created_at)'));
        }, 'sub')
        ->get()
        ->max('max_minutes');

    $averageMinutesPerDayLastMonth = SaleActivity::selectRaw('SUM(TIME_TO_SEC(TIMEDIFF(end, start)) / 60) / COUNT(DISTINCT DATE(created_at)) as average_minutes')
        ->where('user_id', $user->id)
        ->where('type', 'Llamada')
        ->whereBetween('created_at', [$firstDayLastMonth, $lastDayLastMonth])
        ->first()->average_minutes ?? 0;

    return [
        'minutesToday' => (float) number_format($totalMinutesToday, 2, '.', ''),
        'maxMinutesLastMonth' => (float) number_format($maxMinutesLastMonth, 2, '.', ''),
        'averageMinutesPerDayLastMonth' => (float) number_format($averageMinutesPerDayLastMonth, 2, '.', ''),
    ];
}


public function getCallStats($user)
{
    $now = Carbon::now();

    // Obtener llamadas del día de hoy
    $totalCallsToday = LeadAssignment::where('user_id', $user->id)
        ->whereDate('created_at', $now)
        ->count();

    // Obtener llamadas para el mes actual
    $totalCallsThisMonth = LeadAssignment::where('user_id', $user->id)
        ->whereMonth('created_at', $now->month)
        ->whereYear('created_at', $now->year)
        ->count();

    // Obtener llamadas para el mes anterior
    $totalCallsLastMonth = LeadAssignment::where('user_id', $user->id)
        ->whereMonth('created_at', $now->subMonth()->month)
        ->whereYear('created_at', $now->year)
        ->count();

    // Obtener llamadas para el año actual
    $totalCallsThisYear = LeadAssignment::where('user_id', $user->id)
        ->whereYear('created_at', $now->year)
        ->count();

    // Calcular promedio de llamadas diarias en el mes actual
    $daysInMonth = date('t'); // Obtener el número de días en el mes actual

    $averageCallsPerDayThisMonth = $totalCallsThisMonth / $daysInMonth;

    // Calcular promedio de llamadas mensuales
    $averageCallsThisMonth = $totalCallsThisYear / Carbon::now()->month;


    // Calcular promedio de llamadas anuales
    $averageCallsThisYear = $totalCallsThisYear / Carbon::now()->year;

    return [
    'totalCallsToday' => $totalCallsToday,
    'totalCallsThisMonth' => $totalCallsThisMonth,
    'totalCallsLastMonth' => $totalCallsLastMonth,
    'totalCallsThisYear' => $totalCallsThisYear,
    'averageCallsPerDayThisMonth' => (float) number_format($averageCallsPerDayThisMonth, 2, '.', ''),
    'averageCallsThisMonth' =>(float) number_format($averageCallsThisMonth, 2, '.', ''),
    'averageCallsThisYear' => (float) number_format($averageCallsThisYear, 2, '.', ''),
    ];

    }

    public function getCallsAndSalesPerWeek(Request $request)
{
    // Obtener el usuario asociado al request
    $user = $request->user();

    // Inicializar un array para contar las llamadas por día de la semana
    $callsPerDay = [];

    // Obtener el inicio y el final de la semana actual
    $startOfWeek = Carbon::now()->startOfWeek();
    $endOfWeek = Carbon::now()->endOfWeek();

    // Obtener todos los registros de LeadAssignment del usuario de la semana actual
    $assignments = LeadAssignment::where('user_id', $user->id)
        ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
        ->get();

    // Llenar los días de la semana con los datos de los registros dentro del rango de fechas
    foreach ($assignments as $assignment) {
        // Obtener la fecha y el día de la semana del registro
        $date = Carbon::parse($assignment->created_at);
        $dayOfWeek = $date->dayOfWeek;

        // Si la fecha ya existe en el array, aumentar el contador de llamadas
        if (isset($callsPerDay[$date->toDateTimeString()])) {
            $callsPerDay[$date->toDateTimeString()]['calls']++;
        } else {
            // Si la fecha no existe, agregarla al array con el contador de llamadas a 1
            $callsPerDay[$date->toDateTimeString()] = [
                'x' => $date->toDateTimeString(),
                'y' => 1,
            ];
        }
    }

    // Llenar los días de la semana que no tienen registros con 0 llamadas
    for ($i = 0; $i < 7; $i++) {
        $date = $startOfWeek->copy()->addDays($i);
        if (!isset($callsPerDay[$date->toDateTimeString()])) {
            $callsPerDay[$date->toDateTimeString()] = [
                'x' => $date->toDateTimeString(),
                'y' => 0,
            ];
        }
    }

    // Ordenar el array por fecha
    ksort($callsPerDay);

    // Convertir el array asociativo en un array numérico
    $callsPerDay = array_values($callsPerDay);

    // Obtener las ventas y llamadas para la semana
    if ($request->has(['start', 'end'])) {
        $start = $request->input('start');
        $end = $request->input('end');
        $salesAndCalls = $this->getSalesAndCalls($user, $start, $end);
    } else {
        $salesAndCalls = $this->getSalesAndCalls($user, $startOfWeek, $endOfWeek);
    }

    // Crear el array de datos de respuesta
    $data = [
        'callsPerDay' => $callsPerDay,
        'salesAndCalls' => $salesAndCalls
    ];

    // Retornar la respuesta en formato JSON
    return ApiResponseController::response("Éxito", 200, $data);
}


 public function getSalesAndCalls($user, $start, $end)
{
    // Inicializar arrays para almacenar los datos de ventas y llamadas por día
    $salesPerDay = [];
    $callsPerDay = [];

    // Obtener el rango de fechas según los parámetros proporcionados o la semana actual
    if ($start && $end) {
        $startDate = Carbon::parse($start)->startOfDay();
        $endDate = Carbon::parse($end)->endOfDay();
    } else {
        $startDate = Carbon::now()->startOfWeek();
        $endDate = Carbon::now()->endOfWeek();
    }

    // Obtener los registros de llamadas del usuario dentro del rango de fechas
    $calls = LeadAssignment::where('user_id', $user->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Iterar sobre los registros de llamadas y llenar el array con los datos por día
    foreach ($calls as $call) {
        $date = Carbon::parse($call->created_at)->toDateString();
        if (!isset($callsPerDay[$date])) {
            $callsPerDay[$date] = 0;
        }
        $callsPerDay[$date]++;
    }

    // Obtener los registros de ventas del usuario dentro del rango de fechas desde la tabla orders
    $sales = Order::where('created_by', $user->id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->get();

    // Iterar sobre los registros de ventas y llenar el array con los datos por día
    foreach ($sales as $sale) {
        $date = Carbon::parse($sale->created_at)->toDateString();
        if (!isset($salesPerDay[$date])) {
            $salesPerDay[$date] = 0;
        }
        $salesPerDay[$date]++;
    }

    // Completar las fechas faltantes con 0 llamadas y 0 ventas
    $currentDate = $startDate;
    while ($currentDate <= $endDate) {
        $dateString = $currentDate->toDateString();
        if (!isset($callsPerDay[$dateString])) {
            $callsPerDay[$dateString] = 0;
        }
        if (!isset($salesPerDay[$dateString])) {
            $salesPerDay[$dateString] = 0;
        }
        $currentDate->addDay();
    }

    // Preparar los datos en el formato deseado
    $formattedCalls = [];
    $formattedSales = [];
    foreach ($callsPerDay as $date => $count) {
        $formattedCalls[] = ['x' => $date, 'y' => $count];
    }
    foreach ($salesPerDay as $date => $count) {
        $formattedSales[] = ['x' => $date, 'y' => $count];
    }

    // Ordenar los arrays por fecha
    usort($formattedCalls, function($a, $b) {
        return strtotime($a['x']) - strtotime($b['x']);
    });
    usort($formattedSales, function($a, $b) {
        return strtotime($a['x']) - strtotime($b['x']);
    });

    return [
        'calls' => $formattedCalls,
        'sales' => $formattedSales,
    ];
}









    public function createStudentFromLead(Request $request, $lead_id, $lead_assignment_id)
    {
        $userId = auth()->user()->id;
        $student             = new Student();
        $student->name       = $request->input('name');
        $student->country_id = $request->input('country_id');

        $student->phone            = $request->input('phone');
        $student->document         = $request->input('document');
        $student->document_type_id = $request->input('document_type_id');
        $student->email            = $request->input('email');
        $student->lead_id          = $lead_id;
        $student->user_id          = $userId;
        $student->save();


        $studentId = $student->id;

        DB::table('user_student')->insert([
            'user_id' => $userId,
            'student_id' => $studentId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Save lead
        $lead = Lead::where('id', $lead_id)->update([
            'name'             => $request->name,
            'courses'          => $request->courses,
            'phone'            => $request->phone,
            'status'           => $request->status,
            'email'            => $request->email,
            'origin'           => $request->origin,
            'document'         => $request->document,
            'country_id'       => $request->country_id,
            // 'city_id'          => $request->city_id,
            'document_type_id' => $request->document_type_id,
            'user_id'          => $userId,
        ]);

        $leadAssignament = LeadAssignment::where('id', $lead_assignment_id)
            ->with('lead.user')
            ->with(['lead.saleActivities' => function ($query) {
                return $query->with('user')->orderBy('id', 'DESC');
            }])
            ->with(['saleActivities.user' => function ($query) {
                return $query->orderBy('id', 'DESC');
            }])
            ->with('lead.student')
            ->first();




        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }
}
