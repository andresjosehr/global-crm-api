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

        $student = Student::where('id', 496)->with('users', 'orders')->first();
        $order = Order::where('id', $student->orders[0]->id)->with('orderCourses.course', 'dues', 'student.users', 'currency')->first();

        StudentsController::dipatchNotification($order, $student);

        return ["Exito"];


        return [
            'order' => $order,
            'student' => $student
        ];
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
