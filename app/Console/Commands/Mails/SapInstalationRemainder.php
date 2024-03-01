<?php

namespace App\Console\Commands\Mails;

use App\Models\SapInstalation;
use Illuminate\Console\Command;

class SapInstalationRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send-sap-instalation-remainder';

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
        // $sapInstalations = SapInstalation::with('lastSapTry')
        //                     ->whereHas('lastSapTry',)
    }
}
