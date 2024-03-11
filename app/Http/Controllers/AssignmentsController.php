<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\SapInstalation;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssignmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $assignments = Assignment::with('user')
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);


        // Inicio alumnos que empiezan clase dentro de 72 horas y aun no se les ha enviado link de agendamiento
        $students = Student::with('orders.orderCourses', 'orders.dues')
            ->whereHas('orders', function ($query) {
                $query->whereHas('orderCourses', function ($query) {
                    $query->where('start', '<=', Carbon::now()->addHours(72)->format('Y-m-d'))
                        ->where('start', '>=', Carbon::now()->format('Y-m-d'));
                });
            })
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->get()->filter(function ($student) {
                $amount_payed = $student->orders->last()->dues->where('paid', 1)->sum('amount');
                if ($amount_payed < $student->orders->last()->price_amount) {
                    return false;
                }
                return true;
            })->values()->pluck('id')->toArray();

        $sapInstalationLinkNotSent = SapInstalation::with('lastSapTry', 'student.user')
            ->where('status', 'Pendiente')
            ->whereHas('lastSapTry', function ($query) {
                $query->where('status', 'Por programar')
                    ->whereNull('link_sent_at');
            })

            ->whereHas('student', function ($query) use ($students) {

                $query->whereIn('students.id', $students);
            })
            ->get();
        // Fin alumnos que empiezan clase dentro de 72 horas y aun no se les ha enviado link de agendamiento



        $sapInstalationNotSchedule = SapInstalation::with(['lastSapTry', 'student.user', 'student.liveConnectMessages' => function ($query) {
            return $query->where('message_type', 'LIKE', 'SAP_INSTALATION_REMAINDER_%')
                ->whereCreatedAt('>', Carbon::now()->subDays(2))
                ->orderBy('created_at', 'desc');
        }])
            ->whereHas('lastSapTry', function ($query) {
                return $query->where('status', 'Por programar')
                    ->whereNull('link_sent_at')
                    ->where('start_datetime', '<=', Carbon::now()->addHours(24)->format('Y-m-d H:i:s'))
                    ->whereDate('start_datetime', '>=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->get();




        $data = [
            'sapInstalationLinkNotSent' => $sapInstalationLinkNotSent,
            'sapInstalationNotSchedule'  => $sapInstalationNotSchedule,
            'assignments'     => $assignments
        ];



        return ApiResponseController::response('Exitoso', 200, $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($data)
    {
        $assignment = new Assignment();
        // GET FILLABLE FIELDS
        $fillable = $assignment->getFillable();
        $sapData = collect($data)->only($fillable)->toArray();

        $assignment->fill($sapData);
        $assignment->save();

        return $assignment;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $assignment = Assignment::find($id);
        $assignment->resolved_at = now();
        $assignment->save();

        return ApiResponseController::response('Exitoso', 200, $assignment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function getAllAssignments(Request $request)
    {
    }
}
