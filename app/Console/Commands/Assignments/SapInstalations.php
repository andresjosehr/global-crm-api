<?php

namespace App\Console\Commands\Assignments;

use App\Models\SapInstalation;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SapInstalations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assignments:sap-instalation';

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
        $sap = SapInstalation::with(['lastSapTry', 'student.orders.orderCourses', 'student.liveConnectMessages' => function ($query) {
            return $query->where('message_type', 'LIKE', 'SAP_INSTALATION_REMAINDER_%')
                ->whereCreatedAt('>', Carbon::now()->subDays(2))
                ->orderBy('created_at', 'desc');
        }])
            ->whereHas('lastSapTry', function ($query) {
                return $query->where('status', 'Por programar')
                    ->whereNull('link_sent_at')
                    ->where('start_datetime', '<=', Carbon::now()->addHours(16)->format('Y-m-d H:i:s'))
                    ->whereDate('start_datetime', '>=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->get();

        // Filter where liveConnectMessages is graten than 2




        $this->info($sap->pluck('student.email'));
    }
}
