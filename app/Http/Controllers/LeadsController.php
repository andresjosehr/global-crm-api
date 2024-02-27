<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\User;
use App\Models\Order;
use App\Models\Student;
use App\Models\SaleActivity;
use Illuminate\Http\Request;
use App\Models\LeadAssignment;
use App\Models\LeadObservation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Carbon\CarbonPeriod;

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

        return ApiResponseController::response("Exito", 200, [
            'leadAssignament'  => $leadAssignament,
            'nextScheduleCall' => self::getNextScheduleCall($request),
            'lastCallActivity' => self::getLastCallActivity($request, true),
            'countries'        => Country::all(),
            'documentTypes'    => DocumentType::all(),
            'zadarmaInfo'   => [
                'key'        => $user->zadarma_widget_key,
                'zadarma_id' => $user->zadarma_id,
            ]

        ]);
    }

    public function getManageLeadOptions(Request $request)
    {
        $user = $request->user();
        return ApiResponseController::response("Exito", 200, [
            'nextScheduleCall' => self::getNextScheduleCall($request),
            'lastCallActivity' => self::getLastCallActivity($request, true),
            'countries'        => Country::all(),
            'documentTypes'    => DocumentType::all(),
            'zadarmaInfo'   => [
                'key'        => $user->zadarma_widget_key,
                'zadarma_id' => $user->zadarma_id,
            ]

        ]);
    }

    public function getNextLead(Request $request)
    {

        $user = $request->user();


        self::assignNextLead($user);

        $leadAssignament = $user->leadAssignments()->latest('order')->where('active', true)->with('lead.saleActivities.user', 'lead.user', 'saleActivities.user')->first();

        return ApiResponseController::response("Exito", 200, [
            'leadAssignament' => $leadAssignament,
            'nextScheduleCall' => self::getNextScheduleCall($request)
        ]);
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

        return ApiResponseController::response("Exito", 200, [
            'leadAssignament' => $leadAssignament,
            'nextScheduleCall' => self::getNextScheduleCall($request)
        ]);
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

        $start = microtime(true);

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
            // ->whereHas('leadAssignments', function ($query) use ($round) {
            //     return $query->where('round', $round)->where('lead_id', 'leads.id', false);
            // })
            ->where('status', 'Nuevo')
            ->orderBy('created_at', 'DESC') // Ordenar por fecha de creación, no por ID
            ->limit(2000)
            ->get();

        // Excluir los leads ya asignados al usuario
        $nextLead = $nextLead->filter(function ($lead) use ($assignedLeadsIds) {
            return !$assignedLeadsIds->contains($lead->id);
        })->first();

        // Si no hay leads nuevos, iniciar una nueva ronda
        if (!$nextLead) {
            $round++;
            $nextLead = $this->startNewRound($projects);
        }

        $end = microtime(true);


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
        $lead = Lead::where('id', $id)->first();
        if ($user->role_id == 1) {
            $user_id = $request->user_id;
        }

        if ($user->role_id != 1) {
            if ($request->status == 'Interesado') {
                $user_id = $user->id;
            }

            if ($request->status == 'Matriculado') {
                $user_id = $user->id;
            }

            if ($request->status == 'Potencial') {
                $user_id = $user->id;
            }
        }

        if ($request->status != $lead->status) {
            UserActivity::create([
                'user_id'     => $user->id,
                'description' => 'Cambio de estado del lead ' . $lead->name . ' de ' . $lead->status . ' a ' . $request->status . ' el ' . Carbon::now()->format('Y-m-d H:i:s')
            ]);
        }

        $lead = Lead::where('id', $id)->update([
            'name'             => $request->name,
            'courses'          => $request->courses ?? '',
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

    public function createLead(Request $request)
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

            if ($request->status == 'Matriculado') {
                $user_id = $user->id;
            }
        }
        $lead = Lead::create([
            'name'             => $request->name,
            'courses'          => $request->courses ? $request->courses : '',
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

        $prevLeadAssignment = LeadAssignment::with('lead')->where('user_id', $user->id)->where('active', 1)->first();

        $nextAssignment = LeadAssignment::create([
            'lead_id'     => $lead->id,
            'round'       => $prevLeadAssignment->round,
            'order'       => $prevLeadAssignment->order + 1,
            'active'      => true,                                            // El lead se marca como activo por defecto
            'project_id'  => $prevLeadAssignment->lead->lead_assignment_id,
            'user_id'     => $user->id,
            'assigned_at' => now(),
        ]);


        $nextAssignment->lead()->associate($lead);

        $prevLeadAssignment->update(['active' => false]);

        $nextAssignment = LeadAssignment::where('user_id', $user->id)
            ->where('active', 1)->with('lead', 'lead.user', 'saleActivities.user')->first();


        if ($request->status == 'Matriculado') {
            self::createStudentFromLead($request, $lead->id);
        }

        $lead = Lead::with('observations.user')->with('student')->find($lead->id);

        return ApiResponseController::response("Exito", 200, $nextAssignment);
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

        return $lead;
    }


    public function getLeadsAssignments(Request $request)
    {


        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $leadAssignment = LeadAssignment::with('user', 'lead', 'observations')
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

        $leadAssignment->each(function ($leadAssignment) {
            $leadAssignment->zadarmaStatistics = $leadAssignment->zadarmaStatistics()->get();
        });

        return ApiResponseController::response("Exito", 200, $leadAssignment);
    }


    public function updateSalesActivity(Request $request)
    {
        $user = $request->user();
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
            $lead = Lead::where('id', $request->lead_id)->first();
            if ($request->schedule_call_datetime) {


                UserActivity::create([
                    'user_id'     => $user->id,
                    'description' => 'Llamada programada para el ' . $request->schedule_call_datetime . ' hacia el lead ' . $lead->name . ' con numero ' . $lead->phone . ' el ' . Carbon::now()->format('Y-m-d H:i:s')
                ]);

                $schedule_call_datetime = Carbon::parse($request->schedule_call_datetime);
                // Update lead status as "Interesado"
                Lead::where('id', $request->lead_id)->update([
                    'status' => 'Interesado',
                    'user_id' => $request->user()->id,
                ]);
            } else {

                $user_id = null;
                if ($request->lead_status == 'Potencial' || $request->lead_status == 'Interesado' || $request->lead_status == 'Matriculado') {
                    $user_id = $request->user()->id;

                    if ($lead->status != $request->lead_status) {
                        $lead = Lead::where('id', $request->lead_id)->first();
                        UserActivity::create([
                            'user_id'     => $user->id,
                            'description' => 'Cambio de estado del lead ' . $lead->name . ' de ' . $lead->status . ' a ' . $request->lead_status . ' el ' . Carbon::now()->format('Y-m-d H:i:s')
                        ]);
                    }
                }

                // Update lead status as "No interesado"
                Lead::where('id', $request->lead_id)->update([
                    'status' => $request->lead_status,
                    'user_id' => $user_id,
                ]);
            }




            $updateData = [
                'end' => $end,
                'answered' => $request->answered,
                'observation' => $request->observation,
                'schedule_call_datetime' => $schedule_call_datetime
            ];

            // if observationis not empty in database, does not update it
            if ($callActivity->observation) {
                unset($updateData['observation']);
            }


            $callActivity->update($updateData);

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

        if (count(explode('-', $request->events[0]['channel'])) == 0) {
            return ApiResponseController::response("Exito", 200, []);
        }

        $user_id = explode('-', $request->events[0]['channel'])[1];

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


    public function createStudentFromLead(Request $request, $lead_id, $lead_assignment_id = null)
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
            'courses'          => $request->courses ?? '',
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

        $leadAssignament = null;
        if ($lead_assignment_id) {

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
        }




        return ApiResponseController::response("Exito", 200, $leadAssignament);
    }
}
