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

        // max_execution_time
        ini_set('max_execution_time', -1);

        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);

        $extensions = User::selectRaw("DISTINCT(zadarma_id)")->where('zadarma_id', "IS NOT", NULL)->groupBy('zadarma_id')->get()->pluck('zadarma_id')->toArray();


        foreach ($extensions as $extension) {
            $start = ZadarmaStatistic::where('extension', $extension)->orderBy('callstart', 'desc')->first();

            if (!$start) {
                $start = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d H:i:s');
            } else {
                $start = Carbon::parse($start->callstart)->format('Y-m-d H:i:s');
            }
            $end = Carbon::now()->format('Y-m-d H:i:s');

            $newExtension = explode('-', $extension)[1];
            $records = 1000;

            $i = 1;
            while ($records == 1000) {
                // $records = $this->saveStatistics($statistics->stats);
                $start = Carbon::parse($start)->addDay()->format('Y-m-d H:i:s');
                $statistics = $api->getStatistics(
                    $start,
                    $end,
                    null,
                    $newExtension,
                );

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
                Log::info('Extension: ' . $extension . ' - ' . $records . ' records - Cicle: ' . $i);
                Log::info('Start: ' . $start);
                Log::info('End: ' . $end);
                Log::info('----------------------------------------------------------------');
                sleep(21);

                if ($records == 1000) {
                    $start = Carbon::parse($statistics->stats[count($statistics->stats) - 1]['callstart'])->format('Y-m-d H:i:s');
                }

                $i++;
            }
        }

        return "Exito";



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
}
