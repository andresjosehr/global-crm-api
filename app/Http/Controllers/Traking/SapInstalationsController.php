<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
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
use App\Models\User;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

class SapInstalationsController extends Controller
{

    public function getList(Request $request)
    {
        $user = $request->user();

        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;

        $saps = SapInstalation::with('sapTries', 'student')
            // ->whereHas('sapTries', function ($query) use ($user) {
            //     $query->where('staff_id', $user->id);
            // })
            ->paginate($perPage);

        return ApiResponseController::response('Sap instalations list', 200, $saps);
    }

    public function saveDraft(Request $request)
    {
        $sapInstalation = new SapInstalation();
        $sap = $request->all();

        $sap['key'] = md5(microtime());

        $otherSaps = SapInstalation::where('order_id', $sap['order_id'])->get();

        $sapInstalation->fill($sap);
        $sapInstalation->save();

        $try = new SapTry();
        $try->start_datetime = OrderCourse::where('order_id', $sap['order_id'])->where('type', 'paid')->get()->reduce(function ($carry, $item) {
            return $item->start < $carry ? $item->start : $carry;
        }, Carbon::now()->addDecade()->format('Y-m-d'));

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
        $tryDB = SapTry::where('sap_instalation_id', $sapDB->id)->orderBy('id', 'desc')->first();
        $data   = $request->all();


        unset($data['key']);
        $data['start_datetime'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $request->time['start_time'];
        $data['start_datetime_target_timezone'] = Carbon::parse($data['date'])->format('Y-m-d') . ' ' . $request->time['start_time_in_target_zone'];
        $data['timezone']       = $request->time['timezone'];
        $data['end_datetime']   = Carbon::parse($data['start_datetime'])->addMinutes(30)->format('Y-m-d H:i:s');
        $data['schedule_at']    = self::isSchedule($data);




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

        $data['staff_id'] = $this->findAvailableStaff(Carbon::parse($data['date'])->format('Y-m-d'))->id;

        if (!$tryDB->schedule_at && $data['schedule_at']) {
            $data['status'] = 'Programada';
        }

        $sapTryFillable = (new SapTry())->getFillable();
        $tryData = collect($data)->only($sapTryFillable)->toArray();
        SapTry::where('id', $tryDB->id)->update($tryData);

        // self::triggerSapInstalationEvents($tryDB, $data);



        GeneralJob::dispatch(SapInstalationsController::class, 'triggerSapInstalationEvents', ['trypOld' => $tryDB, 'tryNew' => $data])->onQueue('default');


        $sapDB = SapInstalation::with('sapTries')->where('id', $id)->first();
        return ApiResponseController::response('Sap instalation updated', 200, $sapDB);
    }

    public function isSchedule($sap)
    {
        $fields = [
            "restrictions",
            "sap_user",
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

    public function triggerSapInstalationEvents($trypOld, $tryNew)
    {

        $sap = SapInstalation::with('sapTries', 'student')->whereHas('sapTries', function ($query) use ($trypOld) {
            $query->where('id', $trypOld->id);
        })->first();



        $first = null;

        if (!$trypOld->schedule_at && $tryNew['schedule_at']) {
            $first = true;
        }

        if ($trypOld->start_datetime != $tryNew['start_datetime'] && $trypOld->schedule_at) {
            $first = false;
        }

        if (is_null($first)) {
            return;
        }


        if (!$first) {
            $zoho_data = json_decode($trypOld->zoho_data);
            ZohoService::deleteCalendarEvent($zoho_data->uid, $zoho_data->etag);
        }

        $response = ZohoService::createCalendarEvent($tryNew['start_datetime'], $tryNew['end_datetime'], 'Instalación SAP con alumno ' . $sap->student->name);
        $data = json_decode($response)->events[0];
        // Convert StdClass to Array
        $data = json_decode(json_encode($data), true);
        SapTry::where('id', $trypOld->id)->update(['zoho_data' => $data]);

        $content = view('mails.sap-schedule')->with(['sap' => $sap, 'retry' => !$first])->render();
        CoreMailsController::sendMail('andresjosehr@gmail.com', 'Reagendamiento de instalación', $content);


        $data = [
            "icon"        => 'computer',
            "user_id"     => $sap->staff_id,
            "title"       => $first ? 'Agendamiento de instalación SAP' : 'Reagendamiento de instalación SAP',
            "description" => 'El alumno ' . $sap->student->name . ' ' . $sap->student->last_name . ' ha agendado una instalación SAP',
            "link"        => '/instalaciones-sap/' . $sap->id
        ];
        $data['title'] = $first ? 'Agendamiento de instalación SAP' : 'Reagendamiento de instalación SAP';
        $data['description'] = 'El alumno ' . $sap->student->name . ' ' . $sap->student->last_name . ' ha ' . ($first ? 'agendado' : 'reagendado') . ' una instalación SAP';

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
            return ApiResponseController::response('Unauthorized', 401);
        }

        return ApiResponseController::response('Authorized', 200);
    }

    public function getSapInstalation(Request $request, $key)
    {
        $sapInstalation = SapInstalation::where('key', $key)->orWhere('id', $key)
            ->with('student.city', 'student.state', 'staff')
            ->first();

        if (!$sapInstalation) {
            return ApiResponseController::response('No sap instalation found', 404);
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

                $instalations = SapTry::selectRaw('MAX(id) as id, sap_instalation_id')
                    ->where('staff_id', $technician->id)
                    ->groupBy('sap_instalation_id')
                    ->pluck('id')
                    ->toArray();

                $instalations = SapTry::whereIn('id', $instalations)->whereDate('start_datetime', $date)->get();

                $totalInstalationTime = 0;
                foreach ($instalations as $instalation) {
                    $instalationStartTime = Carbon::parse($instalation->start_datetime);
                    $instalationEndTime = Carbon::parse($instalation->end_datetime);
                    $totalInstalationTime += $instalationEndTime->diffInMinutes($instalationStartTime);
                }

                $totalAvailableTime += $endTime->diffInMinutes($startTime) - $totalInstalationTime;
            }

            // Log::info([$technician->id => [
            //     'totalAvailableTime' => $totalAvailableTime,
            //     'maxAvailableTime' => $maxAvailableTime
            // ]]);

            // 4. Seleccionar Técnico con Más Tiempo Disponible
            if ($totalAvailableTime > $maxAvailableTime) {
                $maxAvailableTime = $totalAvailableTime;
                $availableTechnician = $technician;
            }
        }

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

    public function getSapTries(Request $request, $id)
    {
        $sapInstalation = SapTry::with('staff')->where('sap_instalation_id', $id)->get();

        return ApiResponseController::response('Sap instalation tries', 200, $sapInstalation);
    }
}
