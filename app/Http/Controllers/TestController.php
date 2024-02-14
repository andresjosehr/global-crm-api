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
        return ZadarmaStatistic::with('lead', 'user')
            ->whereHas('user', function ($query) {
                $query->where('id', 7);
            })
            ->whereHas('lead', function ($query) {
                $query->where('id', 68794);
            })
            ->get();
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
