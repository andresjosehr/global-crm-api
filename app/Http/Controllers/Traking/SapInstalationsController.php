<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Controllers\NotificationController;
use App\Http\Services\ZohoService;
use App\Jobs\GeneralJob;
use App\Models\Currency;
use App\Models\Due;
use App\Models\Holiday;
use App\Models\Message;
use App\Models\OrderCourse;
use App\Models\PaymentMethod;
use App\Models\Price;
use App\Models\SapInstalation;
use App\Models\SapTry;
use App\Models\StaffAvailabilitySlot;
use App\Models\Student;
use App\Models\User;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Google\Service\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

class SapInstalationsController extends Controller
{

    public function getList(Request $request)
    {


        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;



        $saps = SapInstalation::with('sapTries', 'student', 'lastSapTry.staff', 'due')
            ->when($user->role_id === 5, function ($query) use ($user, $request) {
                $query->whereHas('lastSapTry', function ($query) use ($user) {
                    return $query->where('status', 'Programada')
                        ->where('staff_id', $user->id);
                });
            })
            ->when($request->instalation_date, function ($query) use ($request) {
                $query->whereHas('lastSapTry', function ($query) use ($request) {
                    $query->whereDate('start_datetime', $request->instalation_date);
                });
            })
            ->when($request->status && $request->status != 'Pendiente sin agendar', function ($query) use ($request) {
                $query->where('status', $request->status)
                    ->whereHas('lastSapTry', function ($query) {
                        $query->whereNotNull('schedule_at')
                            ->whereNotNull('start_datetime_target_timezone');
                    });
            })
            ->when($request->status && $request->status == 'Pendiente sin agendar', function ($query) use ($request) {
                return $query->whereHas('lastSapTry', function ($query) {
                    $query->whereNull('schedule_at');
                })->where('status', 'Pendiente');
            })
            ->when($request->searchTerm === 'Pendiente de verificacion de pago', function ($query) {

                $query->whereNotNull('due_id')->whereHas('due', function ($query) {
                    $query->whereNotNull('payment_receipt')
                        ->whereNull('payment_verified_at');
                });
            })
            ->when($request->user_id, function ($query) use ($request) {
                $query->whereHas('lastSapTry', function ($query) use ($request) {
                    $query->where('staff_id', $request->user_id);
                });
            })->when($request->student, function ($query) use ($request) {
                $query->whereHas('student', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->student . '%')
                        ->orWhere('phone', 'like', '%' . $request->student . '%')
                        ->orWhere('email', 'like', '%' . $request->student . '%');
                });
            })
            ->when($user->role_id === 3 || $user->role_id === 4, function ($query) use ($request) {
                $studentsIds = Student::where('user_id', $request->user()->id)->get()->pluck('id');
                $query->whereHas('student', function ($query) use ($studentsIds) {
                    $query->whereIn('students.id', $studentsIds);
                });
            })
            // order by last try start_datetime
            ->paginate($perPage);

        // sort by start_datetime
        // $saps = $saps->sortBy('start_datetime');

        return ApiResponseController::response('Sap instalations list', 200, $saps);
    }

    public function getFromOrder(Request $request, $id)
    {
        $saps = SapInstalation::with('sapTries', 'student', 'due')
            ->where('order_id', $id)
            ->get();

        return ApiResponseController::response('Sap instalations list', 200, $saps);
    }

    public function saveDraft(Request $request)
    {
        $sapInstalation = new SapInstalation();
        $sap = $request->all();

        $sap['key'] = md5(microtime());

        $otherSaps = SapInstalation::where('order_id', $sap['order_id'])
            ->where(function ($query) {
                $query->where('instalation_type', '<>', 'Desbloqueo')
                    ->where('instalation_type', '<>', 'Asignación de usuario y contraseña')
                    ->orWhereNull('instalation_type');
            })
            ->get()
            ->count();

        $sap['status'] = 'Pendiente';

        if ($otherSaps > 1) {
            $due = Due::create(['payment_reason' => 'Instalación SAP',]);
            $sap['due_id'] = $due->id;
        }

        $sapInstalation->fill($sap);
        $sapInstalation->save();

        $try = new SapTry();
        $start_datetime = OrderCourse::where('order_id', $sap['order_id'])
            ->where('type', 'paid')
            ->whereNotNull('start')
            ->get()->reduce(function ($carry, $item) {
                $start = Carbon::parse($item->start);
                return $carry ? ($start->lt($carry) ? $start : $carry) : $start;
            }, Carbon::now()->addDecade()->format('Y-m-d'));



        $now = Carbon::now();
        $start_datetime = $start_datetime->lt($now) ? $now->addDays(2) : $start_datetime;

        $holidays = Holiday::all();
        while ($start_datetime->isSunday() || $holidays->contains('date', $start_datetime->format('Y-m-d'))) {
            $start_datetime->addDay();
        }


        $try->start_datetime = $start_datetime->format('Y-m-d') . ' 00:00:00';

        $try->end_datetime = Carbon::parse($try->start_datetime)->addMinutes(30)->format('Y-m-d H:i:s');

        $try->staff_id           = $this->findAvailableStaff($try->start_datetime)->id;
        $try->sap_instalation_id = $sapInstalation->id;
        $try->status = "Por programar";
        $try->save();

        $sapInstalation = SapInstalation::with('sapTries', 'staff', 'due', 'lastSapTry')->where('id', $sapInstalation->id)->first();
        return ApiResponseController::response('Sap instalation saved', 200, $sapInstalation);
    }


    public function update(Request $request, $id)
    {
        $sapDB = SapInstalation::find($id);
        $data   = $request->all();
        unset($data['key']);

        $sapFillable = (new SapInstalation())->getFillable();
        $sapData = collect($data)->only($sapFillable)->toArray();

        if ($sapData['instalation_type'] === 'Desbloqueo SAP') {
            $sapData['staff_id'] = 30;

            if (!$sapDB->due_id) {
                $due = Due::create(['payment_reason' => 'Desbloqueo SAP',]);
                $sapData['due_id'] = $due->id;
            }
        }

        if ($sapData['instalation_type'] === 'Asignación de usuario y contraseña') {
            if ($sapDB->due_id) {
                Due::where('id', $sapDB->due_id)->delete();
                $sapData['due_id'] = null;
            }
        }

        // iterate over fillable fields
        collect($sapDB->getFillable())->each(function ($field) use ($sapData, $sapDB) {
            if (isset($sapData[$field])) {
                $sapDB->$field = $sapData[$field];
            }
        });
        $sapDB->save();

        $sapDB = SapInstalation::with('sapTries', 'due', 'lastSapTry')->where('id', $id)->first();

        // $data['staff_id'] = $this->findAvailableStaff(Carbon::parse($data['date'])->format('Y-m-d'))->id;

        if ($sapDB->due_id) {
            self::updatePayment($request, $id);
        }

        $sapDB = SapInstalation::with('sapTries', 'due', 'lastSapTry')->where('id', $id)->first();
        return ApiResponseController::response('Sap instalation updated', 200, $sapDB);
    }


    public function isSchedule($sap)
    {
        $fields = [
            "restrictions",
            "screenshot",
            "start_datetime",
            "end_datetime",
            "operating_system",
            "pc_type",
            "previus_sap_instalation",
        ];

        if ($sap['pc_type'] === 'Personal') {
            unset($fields[0]);
        }

        if ($sap['instalation_type'] === 'Instalación completa') {
            unset($fields[1]);
        }

        if ($sap['instalation_type'] !== 'Instalación completa') {

            unset($fields[6]);
        }

        if ($sap['previus_sap_instalation'] === false) {
            unset($fields[1]);
            unset($fields[2]);
        }

        $allFieldsFilled = collect($fields)->reduce(function ($carry, $field) use ($sap) {
            $filled = $carry && array_key_exists($field, $sap) && !is_null($sap[$field]);

            return $filled;
        }, true);

        if ($allFieldsFilled) {
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        return null;
    }

    public function triggerSapInstalationEvents($trypOld, $tryNew, $sapOld, $sapNew)
    {

        $sap = SapInstalation::with('sapTries', 'student.user', 'lastSapTry')->whereHas('sapTries', function ($query) use ($trypOld) {
            $query->where('id', $trypOld->id);
        })->first();



        $first = null;

        if (!$trypOld->schedule_at && $tryNew->schedule_at) {
            $first = true;
        }

        if ($trypOld->start_datetime != $tryNew->start_datetime && $trypOld->schedule_at && $trypOld->schedule_at) {
            $first = false;
        }

        if (is_null($first)) {
            return;
        }


        if (!$first) {
            $zoho_data = json_decode($trypOld->zoho_data);
            ZohoService::deleteCalendarEvent($zoho_data->uid, $zoho_data->etag);
        }


        $aditionalText = '';
        if ($sapOld->restrictions == null && $sapNew->restrictions != null) {
            $aditionalText .= ' | Se han agregado restricciones';
            if ($first) {
                $data = [
                    "icon"        => 'computer',
                    "user_id"     => $sap->student->user_id,
                    "title"       => 'Restricciones agregadas a instalación SAP',
                    "description" => 'El alumno ' . $sap->student->name . ' ha agregado restricciones a su instalación SAP, por favor revisar la información',
                    "link"        => '#',
                ];
                $assignment = new AssignmentsController();
                $assignment->store($data);
            }
        }

        if ($sapOld->previus_sap_instalation == null && $sapNew->previus_sap_instalation == 1) {
            $aditionalText .= ' | Se ha marcado como que ya ha tenido una instalación previa';
            if ($first) {
                $data = [
                    "icon"        => 'computer',
                    "user_id"     => $sap->student->user_id,
                    "title"       => 'Instalación SAP previa marcada',
                    "description" => 'El alumno ' . $sap->student->name . ' ha marcado que ya ha tenido una instalación SAP previa, por favor revisar la información',
                    "link"        => '#',
                ];
                $assignment = new AssignmentsController();
                $assignment->store($data);
            }
        }

        $instalation_type = $sapNew->instalation_type == 'Instalación completa' ? 'Instalación SAP' : $sapNew->instalation_type;
        $instalation_type = $instalation_type ? $instalation_type : 'Instalación SAP';

        $title = $first ? "Agendamiento de $instalation_type" : "Reagendamiento de $instalation_type";


        $attendees = [
            [
                'email'      => $sap->student->email,
                'permission' => "2"
            ]
        ];

        $response = ZohoService::createCalendarEvent($tryNew->start_datetime, $tryNew->end_datetime, 'Instalación SAP con alumno ' . $sap->student->name, $attendees);
        $data = json_decode($response)->events[0];
        // Convert StdClass to Array
        $data = json_decode(json_encode($data), true);
        SapTry::where('id', $trypOld->id)->update(['zoho_data' => $data]);
        $otherSapInstalations = SapInstalation::where('order_id', $sap->order_id)->get();

        $content = view('mails.sap-schedule')->with(['sap' => $sap, 'retry' => !$first, 'otherSapInstalations' => $otherSapInstalations])->render();
        CoreMailsController::sendMail($sap->student->email, $title, $content);



        $title = $title . ' | ' . $sap->student->name;
        $body = 'El alumno ' . $sap->student->name . ' ha ' . ($first ? 'agendado' : 'reagendado') . ' su instalación SAP el ' . Carbon::parse($tryNew->start_datetime)->format('Y-m-d H:i:s') . ' ' . $aditionalText;

        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => $title,
            'body'       => $body,
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => $sap->student->user_id,
            'use_router' => false,
        ]);


        // if Carbon::parse($tryNew->start_datetime) is today
        if (Carbon::parse($tryNew->start_datetime)->isToday()) {
            $noti = new NotificationController();
            $noti = $noti->store([
                'title'      => $title,
                'body'       => $body,
                'icon'       => 'check_circle_outline',
                'url'        => '#',
                'user_id'    => $tryNew->staff_id,
                'use_router' => false,
            ]);
        }
    }

    public function createAssignment($sap, $try, $data)
    {
    }

    // checkScheduleAccess
    public function checkScheduleAccess(Request $request, $key)
    {
        $sapInstalation = SapInstalation::where('key', $key)->first();

        if (!$sapInstalation) {
            return ApiResponseController::response('No found', 404);
        }

        return ApiResponseController::response('Authorized', 200);
    }

    public function getSapInstalation(Request $request, $key)
    {
        $sapInstalation = SapInstalation::where('key', $key)
            ->with('student.city', 'student.state', 'staff', 'sapTries', 'student', 'due')
            ->first();

        if (!$sapInstalation) {
            $sapInstalation = SapInstalation::where('id', $key)
                ->with('student.city', 'student.state', 'staff', 'sapTries', 'due')
                ->first();
        }

        if ($sapInstalation->sapTries) {
            $sapInstalation->sapTry = $sapInstalation->sapTries->last();
        } else {
            $sapInstalation->sapTry = [];
        }

        $sapInstalation->first_install = SapInstalation::where('order_id', $sapInstalation->order_id)
            ->where('instalation_type', 'Instalación')
            ->where('id', '<>', $sapInstalation->id)
            ->count() === 0;

        $sapInstalation->otherSapInstalations = SapInstalation::where('order_id', $sapInstalation->order_id)
            ->where('id', '<>', $sapInstalation->id)
            ->get();

        if (!$sapInstalation) {
            return ApiResponseController::response('No sap instalation found', 404);
        }

        return ApiResponseController::response('Authorized', 200, $sapInstalation);
    }

    static public function findAvailableStaff($date)
    {
        // 1. Obtener todos los técnicos
        $technicians = User::where('role_id', 5)
            ->withCount(['sapSchedules' => function ($query) use ($date) {
                $query->whereDate('start_datetime', $date);
            }])
            ->where('active', 1)->get();


        $technicians = $technicians->map(function ($technician) use ($date) {
            $technician->availabilitySlots = StaffAvailabilitySlot::where('user_id', $technician->id)
                ->where('day', strtolower(Carbon::parse($date)->format('l')))
                ->get();

            $technician->totalAvailableTime = $technician->availabilitySlots->reduce(function ($carry, $slot) use ($date, $technician) {
                $startTime = Carbon::parse($slot->start_time);
                $endTime = Carbon::parse($slot->end_time);

                $minuteDiff = $endTime->diffInMinutes($startTime) * 2;

                return $carry + $minuteDiff;
            }, 0);

            $technician->bussyTime = $technician->sap_schedules_count * 30;

            $technician->totalAvailableTime = $technician->totalAvailableTime - $technician->bussyTime;



            return $technician;
        });
        $availableTechnician = $technicians->filter(function ($technician) {
            return $technician->totalAvailableTime > 0;
        })->sortBy('sap_schedules_count')->first();

        return $availableTechnician;
    }

    public function getAvailableTimes(Request $request, $staff_id, $sap_id)
    {
        $user = User::where('id', $staff_id)->first();

        $availableTimes = $user->getAvailableTimesForDate($request->date, $request->datesBussy); // Reemplaza la fecha con la que deseas trabajar.

        $sap = SapInstalation::with('lastSapTry')->find($sap_id);
        $sapTry = $sap->lastSapTry;
        $time = Carbon::parse($sapTry->start_datetime)->format('H:i:s');
        if (count($availableTimes) === 0 && $time === '00:00:00') {
            // reasignar el staff

            $sapTry->staff_id = self::findAvailableStaff($request->date)->id;
            $sapTry->save();

            $availableTimes = User::find($sapTry->staff_id)->getAvailableTimesForDate($request->date, $request->datesBussy);
        }

        return ApiResponseController::response('Consulta Exitosa', 200, $availableTimes);
    }



    public function getOptions()
    {
        $data = [
            'messages' => Message::all(),
            'currencies' => Currency::all(),
            'paymentMethods' => PaymentMethod::all(),
            'prices' => Price::all()
        ];

        return ApiResponseController::response('Options', 200, $data);
    }


    public function updateFromStudent(Request $request, $id)
    {
        $sapDB = SapInstalation::find($id);
        $tryDB = SapTry::where('sap_instalation_id', $sapDB->id)->orderBy('id', 'desc')->first();
        $data   = $request->all();


        unset($data['key']);
        $data['start_datetime']                 = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $request->time['start_time'];
        $data['start_datetime_target_timezone'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $request->time['start_time_in_target_zone'];
        $data['timezone']                       = $request->time['timezone'];
        $data['end_datetime']                   = Carbon::parse($data['start_datetime'])->addMinutes(30)->format('Y-m-d H:i:s');
        $data['schedule_at']                    = self::isSchedule($data);




        $sapFillable = (new SapInstalation())->getFillable();
        $sapData = collect($data)->only($sapFillable)->toArray();

        $sapInstallation = SapInstalation::find($id);
        if ($sapInstallation) {
            // aquí asignarías cada uno de los atributos de $sapData uno por uno
            $sapInstallation->screenshot = $sapData['screenshot'];
            unset($sapData['screenshot']);
            // repite para otros atributos si los hay
            $sapInstallation->save();
        }
        SapInstalation::where('id', $id)->update($sapData);

        if (!$tryDB->schedule_at && $data['schedule_at']) {
            $data['status'] = 'Programada';
        }

        $sapTryFillable = (new SapTry())->getFillable();
        $tryData = collect($data)->only($sapTryFillable)->toArray();

        if ($request->previus_sap_instalation) {
            $tryData['staff_id'] = 30;
        }

        SapTry::where('id', $tryDB->id)->update($tryData);

        $tryNew = SapTry::where('id', $tryDB->id)->first();
        // self::triggerSapInstalationEvents($tryDB, $data);

        $sapNew = SapInstalation::with('sapTries')->where('id', $id)->first();

        GeneralJob::dispatch(SapInstalationsController::class, 'triggerSapInstalationEvents', ['trypOld' => $tryDB, 'tryNew' => $tryNew, 'sapOld' => $sapDB, 'sapNew' => $sapNew]);


        $sapDB = SapInstalation::with('sapTries')->where('id', $id)->first();
        return ApiResponseController::response('Sap instalation updated', 200, $sapDB);
    }

    public function updatePayment(Request $request, $id)
    {
        $sap = SapInstalation::with('order.student')->where('id', $id)->first();
        $due = Due::find(SapInstalation::find($id)->due_id);
        // only payment_fields
        $data = array_filter($request->all(), function ($key) {
            return in_array($key, ['price_id', 'currency_id', 'payment_receipt', 'payment_method_id', 'amount']);
        }, ARRAY_FILTER_USE_KEY);

        if ($request->payment_date) {
            $due->date = $request->payment_date;
        }

        $due->fill($data);



        $student = Student::with('userAssigned')->where('id', $sap->order->student->id)->with('city', 'state')->first();
        $user = $student->user;

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

        $due->save();

        return ApiResponseController::response('Sap try instalation updated', 200, $sap);
    }

    public function verifiedPayment(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }

        $sapInstalation = SapInstalation::with('due')->where('id', $id)->first();
        $sapInstalation->due->payment_verified_at = Carbon::now();
        $sapInstalation->due->payment_verified_by = $user->id;
        $sapInstalation->due->save();

        return ApiResponseController::response('Sap try instalation updated', 200, $sapInstalation);
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }

        $sapInstalation = SapInstalation::find($id);
        if ($sapInstalation->due_id) {
            Due::where('id', $sapInstalation->due_id)->delete();
        }

        $sapInstalation->sapTries()->delete();
        $sapInstalation->delete();

        return ApiResponseController::response('Sap instalation deleted', 200);
    }


    public function importFormExcel()
    {

        // execution time
        ini_set('max_execution_time', -1);

        $googleSheet = new GoogleSheetController();


        $sheet = '1zyxrdz3Brkm9N7n-1WYDzEja2KGNk5_db0SNQuB56Ac';



        $ranges = ['CURSOS!A1:ZZZ50000'];

        $response = $googleSheet->service->spreadsheets_values->batchGet($sheet, ['ranges' => $ranges]);
        $coursesSheet = $response[0]->getValues();

        // Set headers as keys
        $headers = collect($coursesSheet[0]);
        $data = collect($coursesSheet)->map(function ($row) use ($headers) {
            return collect($row)->mapWithKeys(function ($item, $key) use ($headers) {
                return [$headers[$key] => $item];
            });
        });

        $keys = collect([
            [
                'name' => 'PRIMERA INSTALACION',
                'schedules' => [
                    'PRIMERA REPROGRAMACION',
                    'SEGUNDA REPROGRAMACION',
                    "TERCERA REPROGRAMACION",
                ],
            ], [
                'name' => 'SEGUNDA INSTALACION',
                'schedules' => [
                    "CUARTA REPROGRAMACION",
                    "QUINTA REPROGRAMACION",
                    "SEXTA REPROGRAMACION",
                ],
            ], [
                'name' => 'TERCERA INSTALACION',
                'schedules' => [
                    "SEPTIMA REPROGRAMACION",
                    "OCTAVA REPROGRAMACION",
                    "NOVENA REPROGRAMACION",
                ],
            ], [
                'name' => 'CUARTA INSTALACION',
                'schedules' => [],
            ],
            [
                'name' => 'PRIMERA ASIGNACION DE USUARIO',
                'schedules' => [
                    'PRIMERA REPROGRAMACION',
                    'SEGUNDA REPROGRAMACION',
                    "TERCERA REPROGRAMACION",
                ],
            ],
            [
                'name' => 'SEGUNDA ASIGNACION DE USUARIO',
                'schedules' => [],
            ],
            [
                'name' => 'TERCERA ASIGNACION DE USUARIO',
                'schedules' => [],
            ]

        ]);

        $studentsWithInstalations = $data->reduce(function ($carry, $item) use ($keys) {
            Log::info($item);
            $carry->push([
                'email' => $item['CORREO'],
                'instalations' => $keys->map(function ($instalation) use ($item) {
                    return [
                        'date' => $item[$instalation['name']],
                        // if include
                        'type' => collect(['PRIMERA INSTALACION', 'SEGUNDA INSTALACION', 'TERCERA INSTALACION', 'CUARTA INSTALACION'])->contains($instalation['name']) ? 'Instalación completa' : 'Asignación de usuario y contraseña',
                        'schedules' => collect($instalation['schedules'])->map(function ($schedule) use ($item) {
                            return $item[$schedule];
                        })->filter(function ($schedule) {
                            return $schedule;
                        })
                    ];
                })->filter(function ($instalation) {
                    return $instalation['date'];
                })->values()
            ]);
            // $carry->push($item->order_id);
            return $carry;
        }, collect([]))->filter(function ($student) {
            return $student['instalations']->count() > 0;
        })->values();

        // remove rocio.monserrat08@gmail.com
        $studentsWithInstalations = $studentsWithInstalations->filter(function ($student) {
            return $student['email'] !== 'rocio.monserrat08@gmail.com';
        })->values();

        // return $studentsWithInstalations;


        $studentsEmail = $studentsWithInstalations->map(function ($student) {
            return $student['email'];
        });



        // $studentsDB = Student::whereIn('email', $studentsEmail)->with('sapInstalations')
        //     // WhereHas Sapinstalations at least one
        //     ->whereHas('sapInstalations', function ($query) {
        //         // At least one instalation
        //         $query->whereRaw('true');
        //     })
        //     ->get();

        // // remove students without instalations
        // $studentsDB = $studentsDB->filter(function ($student) {
        //     return $student->sapInstalations->count() > 0;
        // })->values()->map(function ($student) {
        //     return $student->email;
        // });

        // return $studentsDB;





        foreach ($studentsWithInstalations as $student) {
            $studentDB = Student::where('email', $student['email'])->with('orders')->first();

            if (!$studentDB) {
                continue;
            }


            $student['instalations']->each(function ($ins) use ($studentDB) {

                if (!count($studentDB->orders)) {
                    return;
                }

                $sapInstalation                   = new SapInstalation();
                $sapInstalation->order_id         = $studentDB->orders->first()->id;
                $sapInstalation->instalation_type = $ins['type'];
                $sapInstalation->status           = 'Realizada';
                $sapInstalation->key                      = md5(microtime() . rand(100, 999));
                // $sapInstalation->payment_enabled  = 0;
                $sapInstalation->save();



                if ($ins['schedules']->count() > 0) {
                    $ins['schedules']->each(function ($schedule) use ($sapInstalation) {
                        $sapTry                     = new SapTry();
                        $sapTry->start_datetime     = Carbon::createFromFormat('d/m/Y H:i:s', $schedule . ' 10:00:00')->addMinutes(30)->format('Y-m-d H:i:s');;
                        $sapTry->end_datetime       = Carbon::parse($sapTry->start_datetime)->addMinutes(30)->format('Y-m-d H:i:s');
                        $sapTry->staff_id           = rand(28, 30);
                        $sapTry->status             = 'Realizada';
                        $sapTry->sap_instalation_id = $sapInstalation->id;
                        $sapTry->save();
                    });
                } else {
                    $sapTry                     = new SapTry();
                    $sapTry->start_datetime     = Carbon::createFromFormat('d/m/Y H:i:s', $ins['date'] . ' 10:00:00')->addMinutes(30)->format('Y-m-d H:i:s');;
                    $sapTry->end_datetime       = Carbon::parse($sapTry->start_datetime)->addMinutes(30)->format('Y-m-d H:i:s');
                    $sapTry->staff_id           = rand(28, 30);
                    $sapTry->status             = 'Realizada';
                    $sapTry->sap_instalation_id = $sapInstalation->id;
                    $sapTry->save();
                }
            });
        }

        return ['Exito'];
    }


    public function setLinkAsSent(Request $request, $id)
    {
        $user = $request->user();
        if (!collect([1, 2, 3])->contains($user->role_id)) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }
        $sapInstalation = SapInstalation::find($id);
        $sapTry = SapTry::where('id', $sapInstalation->last_sap_try_id)->first();
        if (!$sapTry->link_sent_at) {
            $sapTry->link_sent_at = Carbon::now();
            $sapTry->link_sent_by = $user->id;
            $sapTry->save();
        }

        return ApiResponseController::response('Exito', 200, $sapInstalation);
    }
}
