<?php

namespace App\Console\Commands\Processes;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ZohoService;
use App\Models\SapInstalation;
use App\Models\SapTry;
use Illuminate\Console\Command;

class SendSapInstalationScheduleNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap-instalation-schedule-noti {sap_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sapTry = SapTry::with('sapInstalation')->where('id', $this->argument('sap_id'))->orderBy('id', 'desc')->first();
        $sap = SapInstalation::with('sapTries', 'student.user', 'lastSapTry')->whereHas('sapTries', function ($query) use ($sapTry) {
            $query->where('id', $sapTry->id);
        })->first();

        $instalation_type = $sapTry->instalation_type == 'Instalación completa' ? 'Instalación SAP' : $sapTry->instalation_type;
        $instalation_type = $instalation_type ? $instalation_type : 'Instalación SAP';

        $first = true;

        $title = $first ? "Agendamiento de $instalation_type" : "Reagendamiento de $instalation_type";


        $attendees = [
            [
                'email'      => $sap->student->email,
                'permission' => "2"
            ]
        ];



        $response = ZohoService::createCalendarEvent($sapTry->start_datetime, $sapTry->end_datetime, 'Instalación SAP con alumno ' . $sapTry->sapInstalation->student->name, $attendees);
        $data = json_decode($response)->events[0];
        // Convert StdClass to Array
        $data = json_decode(json_encode($data), true);
        SapTry::where('id', $sapTry->id)->update(['zoho_data' => $data]);
        $otherSapInstalations = SapInstalation::where('order_id', $sap->order_id)->get();

        $content = view('mails.sap-schedule')->with(['sap' => $sap, 'retry' => !$first, 'otherSapInstalations' => $otherSapInstalations])->render();
        CoreMailsController::sendMail($sap->student->email, $title, $content);
    }
}
