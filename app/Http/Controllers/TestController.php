<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Freezing;
use App\Models\OderDateHistory;
use App\Models\Order;
use App\Models\OrderCourse;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $params = [
            'to' => 'andresjosehr@gmail.com',
            'in' => '6271576000000008028',
            // 'subject' => 'Bienvenido(a) a tu curso deSAP PP Planificación de la Producción ¡Global Tecnologías Academy!',
            'fromDate' => '05-Feb-2024',
            'toDate' => '05-Feb-2024',
        ];
        return CoreMailsController::getMails($params);
    }

    // public  sendDebugNotification($user_id)
}
