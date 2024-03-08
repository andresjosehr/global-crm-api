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
        // execution time
        ini_set('max_execution_time', -1);

        $asesores = [
            '14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs' => User::whereName('MC')->first()->id,
            '1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo' => User::whereName('MS')->first()->id,
            '1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI' => User::whereName('GD')->first()->id,
            '15IgSGsDjfrJMLaVRwkpxkusiyNHc0nSaFRpuRJ1ywWk' => User::whereName('LJ')->first()->id
        ];

        $googleSheet = new GoogleSheetController();

        $sheets = DB::table('sheets')
            ->whereNot('sheet_id', '17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA')
            ->where('type', 'prod')
            ->get();

        $allData = [];
        foreach ($sheets as $sheet) {
            $ranges = ['CURSOS!A1:ZZZ50000'];

            $response = $googleSheet->service->spreadsheets_values->batchGet($sheet->sheet_id, ['ranges' => $ranges]);
            $coursesSheet = $response[0]->getValues();

            // Set headers as keys
            $headers = collect($coursesSheet[0]);
            $data = collect($coursesSheet)->map(function ($row) use ($headers) {
                return collect($row)->mapWithKeys(function ($item, $key) use ($headers) {
                    return [$headers[$key] => $item];
                });
            });

            $data = $data->map(function ($item) use ($sheet, $asesores) {
                return [
                    'asesor' => $asesores[$sheet->sheet_id],
                    'email' => $item['CORREO'],
                ];
            });

            $allData = array_merge($allData, $data->toArray());
        }

        foreach ($allData as $data) {
            $student = Student::where('email', $data['email'])->first();
            if ($student) {
                $student->user_id = $data['asesor'];
                $student->save();
            }
        }

        return $allData;
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
