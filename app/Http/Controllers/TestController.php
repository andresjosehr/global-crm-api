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

        $holidays = Holiday::all();

        return Order::with('orderCourses', 'student')->get()->map(function ($order) use ($holidays) {

            $order->orderCourses->map(function ($orderCourse) use ($holidays) {

                $orderCourse->startInfo = [
                    'must_change' => false,
                    'reason' => ''
                ];

                $orderCourse->endInfo = [
                    'must_change' => false,
                    'reason' => ''
                ];

                // Check if the start date is a holiday or sunday
                if ($holidays->contains('date', $orderCourse->start) || Carbon::parse($orderCourse->start)->isSunday()) {
                    $orderCourse->startInfo = [
                        'must_change' => true,
                        'reason' => $holidays->contains('date', $orderCourse->start) ? 'Feriado' : 'Domingo'
                    ];
                }

                // Check if the end date is a holiday or sunday
                if ($holidays->contains('date', $orderCourse->end) || Carbon::parse($orderCourse->end)->isSunday()) {
                    $orderCourse->endInfo = [
                        'must_change' => true,
                        'reason' => $holidays->contains('date', $orderCourse->end) ? 'Feriado' : 'Domingo'
                    ];
                }
                return $orderCourse;
            });
            return $order;
        })->values()->filter(function ($order) {
            return $order->orderCourses->some(function ($orderCourse) {
                return $orderCourse->startInfo['must_change'] || $orderCourse->endInfo['must_change'];
            });
        })->values()->count();
        // ->values()->each(function ($order) use ($holidays) {


        //     for ($i = 0; $i < $order->orderCourses->count(); $i++) {

        //         $orderCourse = $order->orderCourses[$i];
        //         $prevOrderCourse = $i > 0 ? $order->orderCourses[$i - 1] : null;

        //         $start = Carbon::parse($orderCourse->start);
        //         $end = Carbon::parse($orderCourse->end);

        //         Log::info($orderCourse->start);

        //         if (!$orderCourse->start || !$orderCourse->end) {
        //             continue;
        //         }

        //         if ($prevOrderCourse) {
        //             $start = $start->addDays(1);
        //         }

        //         $start = Carbon::parse($start);
        //         Log::info($start);

        //         while ($holidays->contains('date', $start->format('Y-m-d')) || $start->isSunday()) {
        //             $start = Carbon::parse($start)->addDays(1);
        //         }



        //         if ($order->orderCourses->count() > 1 && $order->orderCourses->count() < 5) {
        //             $end = Carbon::parse($start)->addMonths(3);
        //         }

        //         if ($order->orderCourses->count() == 5) {
        //             $end = Carbon::parse($start)->addMonths(12);
        //         }

        //         if ($order->orderCourses->count() == 1) {
        //             $diffMonths = Carbon::parse($end)->diffInMonths($start);
        //             $plusMonths = $diffMonths > 4 ? 6 : 3;
        //             $end = Carbon::parse($start)->addMonths($plusMonths);
        //         }

        //         while ($holidays->contains('date', $end->format('Y-m-d')) || $end->isSunday()) {
        //             $end = Carbon::parse($end)->addDays(1);
        //         }

        //         $orderCourse->start = $start->format('Y-m-d');
        //         $orderCourse->end = $end->format('Y-m-d');

        //         // Remove startInfo and endInfo
        //         unset($orderCourse->startInfo);
        //         unset($orderCourse->endInfo);


        //         $orderCourse->save();
        //     }
        // });

        return "Exito";



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
