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
use App\Models\SapInstalation;
use Illuminate\Support\Facades\Log;
use Resend;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $processesController = new ProcessesController();
        $processesController->updateSellsExcel(1);
    }
}
