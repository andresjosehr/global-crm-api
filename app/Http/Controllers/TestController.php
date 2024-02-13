<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ZadarmaStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Api;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return self::importStatistics();



        // public function getStatistics(
        //     $start = null,
        //     $end = null,
        //     $sip = null,
        //     $costOnly = null,
        //     $type = null,
        //     $skip = null,
        //     $limit = null
        // )
    }
    public function importStatistics()
    {

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
                // $records = $this->saveStatistics($statistics->stats);
                $statistics = $api->getStatistics($start, $end, null, $newExtension, null, null, $skiping);
                Log::info('start: ' . $start);
                Log::info('end: ' . $end);

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
                Log::info('Extension: ' . $extension . "($j/" . count($extensions) . ")");
                Log::info('Skiped: ' . $skiping);
                Log::info('Complete start date - Complete end date: ' . $start . ' - ' . $end);
                Log::info('Cicle start date: ' . $statistics->stats[0]['callstart']);
                Log::info('Cicle end date: ' . $statistics->stats[$records - 1]['callstart']);
                Log::info('----------------------------------------------------------------');
                sleep(22);

                $skiping += 1000;

                $i++;
            }
            $j++;
        }

        return "Exito";



        // public function getStatistics(
        //     $start = null,
        //     $end = null,
        //     $sip = null,
        //     $newExtension = null,
        //     $costOnly = null,
        //     $type = null,
        //     $skip = null,
        //     $limit = null
        // )
    }
}
