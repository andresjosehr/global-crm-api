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

        $extensions = User::selectRaw("DISTINCT(zadarma_id)")->where('zadarma_id', "IS NOT", NULL)
            // ->where('zadarma_id', "328959-710")
            ->groupBy('zadarma_id')->get()->pluck('zadarma_id')->toArray();

        $j = 0;
        foreach ($extensions as $extension) {
            $skiping = 0;
            $start = ZadarmaStatistic::where('extension', $extension)->orderBy('callstart', 'desc')->first();

            if (!$start) {
                $start = Carbon::now()->startOfMonth()->format('Y-m-d H:i:s');
            } else {
                $start = Carbon::parse($start->callstart)->format('Y-m-d H:i:s');
            }
            $end = Carbon::now()->format('Y-m-d H:i:s');

            $newExtension = explode('-', $extension)[1];

            $i = 1;
            while (true) {

                // Log::info('----------------------------------------------------------------');

                $seconds = abs(microtime(true) - $microtime);
                $microtime = microtime(true);

                // Log::info('Seconds: ' . $seconds);
                // if seconds are less than 21 seconds, sleep for the remaining time
                if ($seconds < 21) {
                    // Log::info('Sleeping for: ' . (21 - $seconds));
                    sleep(21 - $seconds);
                }

                // $records = $this->saveStatistics($statistics->stats);
                $statistics = $api->getStatistics($start, $end, null, $newExtension, null, null, $skiping);

                if (count($statistics->stats) == 0) {
                    break;
                }


                // Attatch the extension to the statistics
                $k = 0;
                // return $statistics->stats[0];
                foreach ($statistics->stats as $stat) {
                    $statistics->stats[$k]['extension'] = $extension;
                    $statistics->stats[$k]['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                    // remove id
                    $statistics->stats[$k]['z_id'] = $stat['id'];

                    unset($statistics->stats[$k]['id']);
                    $k++;
                }



                ZadarmaStatistic::insert($statistics->stats);


                $records = count($statistics->stats);
                // Log::info('Extension: ' . $extension . "($j/" . count($extensions) . ")");
                // Log::info('Skiped: ' . $skiping);
                // Log::info('Complete start date - Complete end date: ' . $start . ' - ' . $end);
                // Log::info('Cicle start date: ' . $statistics->stats[0]['callstart']);
                // Log::info('Cicle end date: ' . $statistics->stats[$records - 1]['callstart']);
                // Log::info('Now: ' . Carbon::now()->format('Y-m-d H:i:s'));


                $skiping += 1000;

                $i++;
            }
            $j++;
        }
        return Command::SUCCESS;
    }
}
