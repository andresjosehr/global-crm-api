<?php

namespace App\Console\Commands\Liveconnect;

use App\Http\Services\LiveConnectService;
use App\Jobs\GeneralJob;
use App\Models\Holiday;
use App\Models\SapInstalation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SapInstalationRemainder extends Command
{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Set arguments and options
    protected $signature = 'liveconnect:send-sap-instalation-remainders {type}';

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
        // Get type
        $type = $this->argument('type');

        if (!$type) {
            $this->error('Type is required');
            return;
        }

        if ($type == 'last_day') {

            GeneralJob::dispatch(SapInstalationRemainder::class, 'lastDay', [])->onQueue('liveconnect');
        }
    }



    public function lastDay()
    {

        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        SapInstalation::with('lastSapTry', 'order.student')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) use ($nextDay) {
                $query->where('status', 'Por programar')
                    ->whereDate('start_datetime', $nextDay->format('Y-m-d'));
            })
            ->get()->each(function ($instalation) {
                $student_id = $instalation->order->student->id;
                $phone = $instalation->order->student->phone;
                $message = "Hola, " . $instalation->order->student->name . " te recordamos que tienes una instalación de SAP pendiente para el día de mañana. Por favor, confirma tu disponibilidad para la instalación. Gracias.";
                Log::info('Message: ' . $message);
                $liveconnectService = new LiveConnectService();
                $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'SAP_INSTALATION_REMAINDER_LAST_DAY');
                sleep(rand(6, 12));
            });

        sleep(rand(8, 15));
    }
}
