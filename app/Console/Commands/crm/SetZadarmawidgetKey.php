<?php

namespace App\Console\Commands\crm;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\TryCatch;
use Zadarma_API\Api;

class SetZadarmawidgetKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zadarma:set-widget-key';

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
        $extensions = User::selectRaw("DISTINCT(zadarma_id)")->where('zadarma_id', "IS NOT", NULL)->groupBy('zadarma_id')->get()->pluck('zadarma_id')->toArray();

        $i = 1;
        foreach ($extensions as $extension) {


            try {
                $key    = env('ZADARMA_KEY');
                $secret = env('ZADARMA_SECRET');

                $api = new Api($key, $secret);
                $sip = $api->getWebrtcKey($extension);

                User::where('zadarma_id', $extension)->update([
                    'zadarma_widget_key' => $sip->key
                ]);
                sleep(21);
            } catch (\Throwable $th) {
                //throw $th;

                Log::error('Error updating widget key for extension: ' . $extension);
                Log::error($th->getMessage());
            }
            $i++;
            Log::info('Zadarma widget key updated: (' . $i . '/' . count($extensions) . ')');
            Log::info('Extension: ' . $extension);
        }
        return Command::SUCCESS;
    }
}
