<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
        $microtime = 0;
        // max_execution_time
        ini_set('max_execution_time', -1);

        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);

        $extensions = User::selectRaw("DISTINCT(zadarma_id)")->where('zadarma_id', "IS NOT", NULL)
            // ->where('zadarma_id', "328959-710")
            ->groupBy('zadarma_id')->get()->pluck('zadarma_id')->toArray();

        $start = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::now()->format('Y-m-d H:i:s');

        $statistics = $api->getPbxStatistics($start, $end);

        return [$statistics];
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
