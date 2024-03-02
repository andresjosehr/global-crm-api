<?php

namespace App\Console\Commands\Liveconnect;

use App\Jobs\GeneralJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendUnfreezingRemainder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liveconnect:send-unfreezing-remainder';

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
        GeneralJob::dispatch(SendUnfreezingRemainder::class, 'sendMessages', [])->onQueue('liveconnect');

        GeneralJob::dispatch(SendUnfreezingRemainder::class, 'sendMessages', [])->onQueue('default');
    }

    public function sendMessages()
    {
        Log::info('Sending unfreezing remainder');
        for ($i = 0; $i < 5; $i++) {
            Log::info('Sending unfreezing remainder ' . $i);
            sleep(1);
        }
        Log::info('Unfreezing remainder sent');
        sleep(5);
    }
}
