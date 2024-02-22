<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ZohoService;
use App\Models\Currency;
use App\Models\Due;
use App\Models\Order;
use App\Models\SapInstalation;
use App\Models\Student;
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

        $noti = new NotificationController();
        $noti = $noti->store([
            'title'      => 'Titulo de prueba',
            'body'       => 'Esta es una prueba',
            'icon'       => 'check_circle_outline',
            'url'        => '#',
            'user_id'    => 6,
            'use_router' => false,
            'custom_data' => [
                []
            ]
        ]);

        return "Epa";
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
