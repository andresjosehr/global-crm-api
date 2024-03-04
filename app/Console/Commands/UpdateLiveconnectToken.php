<?php

namespace App\Console\Commands;

use App\Http\Services\LiveConnectService;
use App\Models\Token;
use Illuminate\Console\Command;

class UpdateLiveconnectToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'liveconnect:update-token';

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
        $live = new LiveConnectService();
        $token = $live->getToken()->PageGearToken;

        // update or create
        $liveToken = Token::where('service', 'liveconnect')->first();

        if ($liveToken) {
            $liveToken->update(['token' => $token]);
        } else {
            Token::create([
                'service' => 'liveconnect',
                'token' => $token,
                'environment' => 'production'
            ]);
        }
    }
}
