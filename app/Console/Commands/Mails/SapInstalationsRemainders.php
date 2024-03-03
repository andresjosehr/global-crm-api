<?php

namespace App\Console\Commands\Mails;

use App\Http\Controllers\GoogleSheetController;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Jobs\GeneralJob;
use App\Models\Holiday;
use App\Models\SapInstalation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendUnfreezingMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-sap-instalation-remainders {type}';

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

        GeneralJob::dispatch(SapInstalationRemainder::class, 'sendSapInstalationReminder', ['type' => $type])->onQueue('liveconnect');
    }

    public function sendSapInstalationReminder($type)
    {
        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        // $index = [
        //     'daily' => $this->texts['daily'][Carbon::now()->dayOfWeek],
        //     'penultimate' => $this->messagePenultimateDat(),
        //     'last_day' => $this->messagesLastDay()
        // ];

        SapInstalation::with('lastSapTry', 'order.student')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) use ($nextDay, $type) {
                $query->whereNull('schedule_at')
                    ->when($type == 'daily', function ($query) use ($nextDay) {
                        $query->whereDate('start_datetime', '>', $nextDay->format('Y-m-d'));
                    })
                    ->when($type == 'penultimate', function ($query) use ($nextDay) {
                        $query->whereDate('start_datetime', $nextDay->format('Y-m-d'));
                    })
                    ->when($type == 'last_day', function ($query) {
                        $query->whereDate('start_datetime', Carbon::now()->format('Y-m-d'));
                    });
            })->get();
        //->each(function ($instalation) use ($type, $index) {
        //    $student_id = $instalation->order->student->id;
        //    $phone      = $instalation->order->student->phone;
        //
        //    $message    = $index[$type];
        //
        //    $instalation_type = $instalation->instalation_type == 'Instalación completa' ? 'instalación SAP' : $instalation->instalation_type;
        //    $instalation_type = $instalation_type ? $instalation_type : 'instalación SAP';
        //    $message          = str_replace('{instalation_type}', $instalation_type, $message);
        //
        //    $liveconnectService = new LiveConnectService();
        //    $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', 'SAP_INSTALATION_REMAINDER_DAILY');
        //    sleep(rand(6, 12));
        //});

        sleep(rand(8, 15));
    }
}
