<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ImportStudentsService;
use App\Http\Services\ImportStudentsServiceSEG;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Http\Services\ZohoService;
use App\Jobs\GeneralJob;
use App\Models\Currency;
use App\Models\Due;
use App\Models\Holiday;
use App\Models\LiveconnectMessagesLog;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\Price;
use App\Models\SapInstalation;
use App\Models\Student;
use App\Models\User;
use App\Models\ZadarmaStatistic;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

        Due::all()->map(function ($due) {
            $order = Order::withTrashed()->where('id', $due->order_id)->first();
            if (!Student::where('id', $order->student_id)->exists()) {
                $order->student_id = NULL;
            }
            $due->student_id = $order->student_id;
            $due->payment_reason = 'Curso';
            $due->currency_id = $order->currency_id;
            $due->save();
        });
        // return DB::connection('second')->table('sap_instalations')->where('payment_enabled', 1)->get();
        DB::connection('second')->table('sap_instalations')->where('payment_enabled', 1)->get()->map(function ($instalation) {

            $currency_id = null;
            if ($instalation->price_id) {
                $currency_id = Price::where('id', $instalation->price_id)->first()->currency_id;
            }

            $due = Due::create([
                'date'   => $instalation->payment_date,
                'amount' => $instalation->price_amount,
                'payment_method_id' => NULL,
                'currency_id' => $currency_id,
                'price_id' => $instalation->price_id,
                'payment_receipt' => $instalation->payment_receipt,
                'payment_verified_at' => $instalation->payment_verified_at,
                'payment_verified_by' => $instalation->payment_verified_by,
                'payment_reason' => $instalation->instalation_type == 'Desbloqueo SAP' ? 'Desbloqueo SAP' : 'Instalación SAP',
                'student_id' => Order::withTrashed()->where('id', $instalation->order_id)->first()->student_id,
            ]);

            SapInstalation::where('id', $instalation->id)->update(['due_id' => $due->id]);
        });

        return 'Exito';
    }

    public function epale($params = null)
    {
        echo 'yep';
    }

    public function index2()
    {

        event(new \App\Events\CallActivityEvent(1, 'LAST_CALL_ACTIVITY', ["Culito" => 'Esta es una prueba']));
        return;
        // return self::getUserWithCount(null, [3, 4]);

        return Student::with('orders')->get()->filter(function ($student) {
            return $student->orders->count() > 0;
        })->values()->map(function ($student) {
            $student->role = $student->orders[0]->dues->where('paid', 1)->sum('amount') == $student->orders[0]->price_amount ? 4 : 3;
            $student->role_name = $student->role == 4 ? 'Seguimiento' : 'Cobranza';
            return $student;
        })
            ->filter(function ($student) {
                return $student->start_date;
            })->values()
            ->map(function ($student) {

                $user = self::getUserWithCount($student->start_date, [$student->role])->first();
                Student::where('id', $student->id)->update(['user_id' => $user->id]);
                DB::table('user_student')->insert([
                    'student_id' => $student->id,
                    'user_id' => $user->id
                ]);

                return $student;
            });
        return 'Exito';
    }

    public function getUserWithCount($date = null, $roles = [])
    {
        return User::when($roles, function ($query, $roles) {
            return $query->whereIn('role_id', $roles);
        })
            ->withCount('studentsAssigned')->with('students.orders.orderCourses')
            // ->where('role_id', $student->role)
            ->get()->map(function ($user) use ($date) {


                $user->students_assigned_date_count = $user->students->filter(function ($student) use ($date) {
                    if (!$date) {
                        return true;
                    }
                    if ($student->orders->count() > 0) {
                        return $student->orders[0]->orderCourses[0]->start == $date;
                    }
                    return false;
                })->count();
                $user->date = $date;
                unset($user->students);


                return $user;
            })
            ->values()
            ->sortBy([
                ['students_assigned_date_count', 'asc'],
                ['students_assigned_count', 'asc']
            ])->values();
    }
}
