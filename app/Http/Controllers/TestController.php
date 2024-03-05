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
use Illuminate\Support\Facades\Mail;
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
        return SapInstalation::with('lastSapTry')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) {
                $query->where('status', 'Realizada');
            })->get()->map(function ($sapInstalation) {
                $sapInstalation->status = 'Realizada';
                $sapInstalation->save();
                return $sapInstalation;
            })->values();
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
