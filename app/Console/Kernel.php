<?php

namespace App\Console;

use App\Models\SapTry;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $meetings = SapTry::whereDate('start_datetime', '>', Carbon::now()->format('Y-m-d'))
            // where time is not 00:00:00
            ->whereTime('start_datetime', '>', '00:00:00')
            ->get();

        foreach ($meetings as $meeting) {
            $schedule->command('sap:try ' . $meeting->id)
                ->cron(Carbon::parse($meeting->start_datetime)->format('i H d m *'));
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }


    private function sapInstalationAssignments($schedule)
    {
        // each 5 minutes custom function
        $schedule->command('sap:installations-assignments')->everyFiveMinutes();
    }
}
