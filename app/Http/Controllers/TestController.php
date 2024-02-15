<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
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
        CoreMailsController::sendMail('andresjosehr@gmail.com', 'Prueba', 'Prueba');

        return "Exito";
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
