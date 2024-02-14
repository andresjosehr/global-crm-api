<?php

namespace App\Console\Commands\crm;

use App\Models\User;
use App\Models\ZadarmaStatistic;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Api;

class ImportZadarmaStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zadarma:import-statistics';

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
        $microtime = 0;
        // max_execution_time
        ini_set('max_execution_time', -1);

        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);

        $start = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
        $end = Carbon::now()->format('Y-m-d H:i:s');

        $stats = ZadarmaStatistic::orderBy('callstart', 'desc')->first();

        if ($stats) {
            $start = Carbon::parse($stats->callstart)->addSeconds(1)->format('Y-m-d H:i:s');
        }

        $extensions = User::selectRaw('RIGHT(zadarma_id, 3) as extension, zadarma_id')
            ->where('zadarma_id', 'IS NOT', null)
            ->get()->pluck('zadarma_id', 'extension')->toArray();

        $skiping = 0;
        while (true) {



            // $records = $this->saveStatistics($statistics->stats);
            $statistics = $api->getPbxStatistics($start, $end, true, null, $skiping);

            if (count($statistics->stats) == 0) {
                break;
            }


            // Attatch the extension to the statistics
            $k = 0;
            // return $statistics->stats[0];
            foreach ($statistics->stats as $stat) {
                $extension = $statistics->stats[$k]['clid'];
                $extension = explode('(', $extension)[1];
                $extension = explode(')', $extension)[0];

                $statistics->stats[$k]['extension'] = isset($extensions[$extension]) ? $extensions[$extension] : $extension;
                $statistics->stats[$k]['created_at'] = Carbon::now()->format('Y-m-d H:i:s');

                unset($statistics->stats[$k]['id']);
                $k++;
            }



            ZadarmaStatistic::insert($statistics->stats);


            $records = count($statistics->stats);
            Log::info('Skiped: ' . $skiping);
            Log::info('Cicle start - end: ' . $statistics->stats[0]['callstart'] . ' - ' . $statistics->stats[$records - 1]['callstart']);
            Log::info('Now: ' . Carbon::now()->format('Y-m-d H:i:s'));
            Log::info('----------------------------------------------------------------');
            $skiping += 1000;
            sleep(30);
        }
    }
}
