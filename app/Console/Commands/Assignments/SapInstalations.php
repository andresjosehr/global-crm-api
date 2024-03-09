<?php

namespace App\Console\Commands\Assignments;

use App\Models\SapInstalation;
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
        $sap = SapInstalation::with('lastSapTry', 'student.liveConnectMessages')->whereHas('lastSapTry', function ($query) {
            $query->where('status', 'Por programar')
                ->whereNotNull('link_sent_at');
        })
            // Where have student.liveConnectMessages count gratter than 2
            ->whereHas('student.liveConnectMessages', function ($query) {
                $query->havingRaw('COUNT(*) > 2');
            })
            ->get();
        return Command::SUCCESS;
    }
}
