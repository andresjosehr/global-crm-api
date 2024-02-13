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
        session_write_close();
        sleep(20);
        return "Exitoso";
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
