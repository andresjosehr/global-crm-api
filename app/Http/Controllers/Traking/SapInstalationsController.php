<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Controllers\NotificationController;
use App\Http\Services\ZohoService;
use App\Jobs\GeneralJob;
use App\Models\Currency;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

class SapInstalationsController extends Controller
{

    public function getList(Request $request)
    {


        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;

        $latestSapTryIdSub = SapTry::selectRaw('MAX(id) as latest_id, sap_instalation_id')
            ->groupBy('sap_instalation_id')->when($request->instalation_date, function ($query) use ($request) {
                $query->whereDate('start_datetime', $request->instalation_date);
            });

        $saps = SapInstalation::with('sapTries', 'student', 'lastSapTry.staff')
            ->joinSub($latestSapTryIdSub, 'latest_tries', function ($join) {
                $join->on('sap_instalations.id', '=', 'latest_tries.sap_instalation_id');
            })->whereExists(function ($subQuery) use ($user, $request) {
                $subQuery->select(DB::raw(1))
                    ->from('sap_tries')
                    ->whereColumn('sap_tries.id', 'latest_tries.latest_id')
                    ->when($user->role_id === 5, function ($query) use ($user, $request) {
                        $query->where('sap_tries.staff_id', $user->id)
                            ->where('status', 'Programada');
                    })

                    ->when($request->instalation_date, function ($query) use ($request) {
                        $query->whereDate('start_datetime', $request->instalation_date);
                    });
            })

            ->when($user->role_id === 1, function ($query) use ($request) {
                $query->when($request->searchTerm, function ($query) use ($request) {
                    $query->when($request->searchTerm === 'Pendiente de verificacion de pago', function ($query) {
                        $query->whereHas('sapTries', function ($query) {
                            $query->where('payment_enabled', 1)
                                ->where('payment_receipt', 'IS NOT', null)
                                ->where('payment_verified_at', null);
                        });
                    })->orWhere(function ($query) use ($request) {
                        $query->where('payment_enabled', 1)
                            ->where('payment_receipt', 'IS NOT', null)
                            ->where('payment_verified_at', null);
                    });
                    // ->where('payment_verified_at', null);
                });
            })
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            // ->orderBy
            ->paginate($perPage);

        return ApiResponseController::response('Sap instalations list', 200, $saps);
    }

    public function getFromOrder(Request $request, $id)
    {
        $saps = SapInstalation::with('sapTries', 'student')
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
                    ->orWhereNull('instalation_type');
            })
            ->get()
            ->count();

        $sap['status'] = 'Pendiente';

        if ($otherSaps > 1) {
            $sap['payment_enabled'] = 1;
        }

        $sapInstalation->fill($sap);
        $sapInstalation->save();

        $try = new SapTry();
        $start_datetime = OrderCourse::where('order_id', $sap['order_id'])->where('type', 'paid')->get()->reduce(function ($carry, $item) {
            $start = Carbon::parse($item->start);
            return $carry ? ($start->lt($carry) ? $start : $carry) : $start;
        }, Carbon::now()->addDecade()->format('Y-m-d'));

        $now = Carbon::now();
        $start_datetime = $start_datetime->lt($now) ? $now->addDays(2) : $start_datetime;

        $try->start_datetime = $start_datetime->format('Y-m-d') . ' 00:00:00';

        $try->end_datetime = Carbon::parse($try->start_datetime)->addMinutes(30)->format('Y-m-d H:i:s');

        $try->staff_id           = $this->findAvailableStaff($try->start_datetime)->id;
        $try->sap_instalation_id = $sapInstalation->id;
        $try->status = "Por programar";
        $try->save();

        $sapInstalation = SapInstalation::with('sapTries', 'staff')->where('id', $sapInstalation->id)->first();
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
            $sapData['payment_enabled'] = 1;
        }

        if ($sapData['instalation_type'] === 'Asignación de usuario y contraseña') {
            $sapData['payment_enabled'] = 0;
        }

        SapInstalation::where('id', $id)->update($sapData);

        // $data['staff_id'] = $this->findAvailableStaff(Carbon::parse($data['date'])->format('Y-m-d'))->id;

        if ($sapDB->payment_enabled) {
            self::updatePayment($request, $id);
        }

        $sapDB = SapInstalation::with('sapTries')->where('id', $id)->first();
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

        $sap = SapInstalation::with('sapTries', 'student.user')->whereHas('sapTries', function ($query) use ($trypOld) {
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


        $title = $first ? 'Agendamiento de instalación SAP' : 'Reagendamiento de instalación SAP';


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



        $data = [
            "icon"        => 'computer',
            "user_id"     => $sap->staff_id,
            "title"       => $title . $aditionalText,
            "description" => 'El alumno ' . $sap->student->name . ' ' . $sap->student->last_name . ' ' . ($first ? 'agendado' : 'reagendado') . ' agendado una instalación SAP',
            "link"        => '/instalaciones-sap/' . $sap->id
        ];
        $data['title'] = $title;
        $data['description'] = 'El alumno ' . $sap->student->name . ' ' . $sap->student->last_name . ' ha ' . ($first ? 'agendado' : 'reagendado') . ' una instalación SAP';


        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => $title . ' | ' . $sap->student->name,
            'body'       => 'El alumno ' . $sap->student->name . ' ha ' . ($first ? 'agendado' : 'reagendado') . ' su instalación SAP' . $aditionalText,
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => $sap->student->user_id,
            'use_router' => false,
        ]);

        $assignment = new AssignmentsController();
        $assignment->store($data);
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
        $sapInstalation = SapInstalation::where('key', $key)->orWhere('id', $key)
            ->with('student.city', 'student.state', 'staff', 'sapTries')
            ->first();

        if ($sapInstalation->sapTries) {
            $sapInstalation->sapTry = $sapInstalation->sapTries->last();
        } else {
            $sapInstalation->sapTry = [];
        }

        $sapInstalation->first_install = SapInstalation::where('order_id', $sapInstalation->order_id)
            ->where('instalation_type', 'Instalación')
            ->where('id', '<>', $sapInstalation->id)
            ->count() === 0;

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

    public function getAvailableTimes(Request $request, $id)
    {
        $user = User::where('id', $id)->first();

        $availableTimes = $user->getAvailableTimesForDate($request->date, $request->datesBussy); // Reemplaza la fecha con la que deseas trabajar.

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

        // $data['staff_id'] = $this->findAvailableStaff(Carbon::parse($data['date'])->format('Y-m-d'))->id;

        if (!$tryDB->schedule_at && $data['schedule_at']) {
            $data['status'] = 'Programada';
        }

        $sapTryFillable = (new SapTry())->getFillable();
        $tryData = collect($data)->only($sapTryFillable)->toArray();
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
        $sapPayment = SapInstalation::with('order.student')->where('id', $id)->first();
        // only payment_fields
        $data = array_filter($request->all(), function ($key) {
            return in_array($key, ['price_id', 'currency_id', 'payment_receipt', 'payment_method_id', 'payment_date']);
        }, ARRAY_FILTER_USE_KEY);

        $sapPayment->fill($data);

        if ($data['price_id']) {
            $sapPayment->price_amount = Price::find($data['price_id'])->amount;
        }

        $student = Student::with('userAssigned')->where('id', $sapPayment->order->student->id)->with('city', 'state')->first();
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

        $sapPayment->save();


        $data = [
            "icon"        => 'computer',
            "user_id"     => $sapPayment->order->student->user_id,
            "title"       => $sapPayment->order->student->name,
            "description" => 'El alumno ' . $sapPayment->order->student->name . ' ha realizado el pago de la instalación, por favor revisar el comprobante de pago y confirmar la instalación.',
            "link"        => '#',
        ];
        $assignment = new AssignmentsController();
        $assignment->store($data);

        return ApiResponseController::response('Sap try instalation updated', 200, $sapPayment);
    }

    public function verifiedPayment(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }

        $sapInstalation = SapInstalation::find($id);
        $sapInstalation->payment_verified_at = Carbon::now();
        $sapInstalation->payment_verified_by = $user->id;
        $sapInstalation->save();

        return ApiResponseController::response('Sap try instalation updated', 200, $sapInstalation);
    }

    public function delete(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role_id !== 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 400);
        }

        SapTry::where('sap_instalation_id', $id)->delete();
        SapInstalation::where('id', $id)->delete();

        return ApiResponseController::response('Sap instalation deleted', 200);
    }
}
