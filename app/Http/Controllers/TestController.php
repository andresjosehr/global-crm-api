<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ZohoService;
use App\Models\Currency;
use App\Models\Due;
use App\Models\Order;
use App\Models\SapInstalation;
use App\Models\User;
use App\Models\ZadarmaStatistic;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Api;
use GuzzleHttp;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        return ZohoService::deleteCalendarEvent('f69956629d9e4d748b121b1d305d5022@zoho.com', $etag = '1708306217689');

        $start = '2024-02-18 22:00:00';
        $end = '2024-02-18 22:20:00';
        // return ZohoService::createCalendarEvent($start, $end);
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
