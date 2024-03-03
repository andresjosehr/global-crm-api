<?php

namespace App\Console\Commands\Mails;

use App\Http\Services\ResendService;
use App\Models\Holiday;
use App\Models\SapInstalation;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SapInstalationRemainder extends Command
{

    private $mails = [
        'daily' => [

            1,
            2,
            3,
            4,
            5,
            6,
        ],
        'penultimate' => [

            1,
            2,
            3,
            4,
        ],
        'last_day' => [

            1,
            2,
        ]
    ];




    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:send-sap-instalation-remainder {type}';

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

        $type = $this->argument('type');

        if (!$type) {
            $this->error('Type is required');
            return;
        }

        $nextDay = Carbon::now()->addDay();

        $holidays = Holiday::all();

        // or sunday
        while ($holidays->contains($nextDay->format('Y-m-d')) || $nextDay->isSunday()) {
            $nextDay->addDay();
        }

        $mails = SapInstalation::with('lastSapTry', 'order.student')
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
            })->get()->map(function ($instalation) use ($type) {
                // $student_id = $instalation->order->student->id;

                $instalation_type = $instalation->instalation_type == 'Instalación completa' ? 'instalación SAP' : $instalation->instalation_type;
                $instalation_type = $instalation_type ? $instalation_type : 'instalación SAP';

                if ($type == 'daily') {
                    $html = view("mails.remainders.sap-instalations.$type." . $this->mails[$type][Carbon::now()->dayOfWeek])->with(['sap' => $instalation])->render();
                } else {
                    $html = view("mails.remainders.sap-instalations.$type." . $this->mails[$type][$this->hourBlocks(Carbon::now()->hour)])->with(['sap' => $instalation])->render();
                }

                return [
                    'from'    => 'No contestar <noreply@globaltecnoacademy.com>',
                    'to'      => [$instalation->order->student->email],
                    'subject' => 'Recordatorio de agendamiento SAP',
                    'html'    => $html,
                ];
            });

        Log::info($mails->toArray());
    }


    private function hourBlocks($hour)
    {
        if ($hour >= 6 && $hour < 10) {
            return 0;
        }
        if ($hour >= 10 && $hour < 12) {
            return 1;
        }
        if ($hour >= 12 && $hour < 14) {
            return 2;
        }
        if ($hour >= 14 && $hour < 16) {
            return 3;
        }
    }
}
