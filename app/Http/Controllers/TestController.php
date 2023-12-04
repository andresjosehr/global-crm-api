<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sales_activities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $salesUsers = User::where('role_id', 2)->with('leadAssignments')->get();
        $faker = \Faker\Factory::create();

        foreach ($salesUsers as $user) {
            for ($i = 0; $i < count($user->leadAssignments) - 1; $i++) {
                $leadAssignment = $user->leadAssignments[$i];
                $nextLeadAssignment = $user->leadAssignments[$i + 1];

                $time1 = Carbon::parse($leadAssignment->created_at);
                $time2 = Carbon::parse($nextLeadAssignment->created_at);

                $diff = $time1->diffInSeconds($time2);
                $n = $faker->numberBetween($min = 1, $max = 3);

                $lastEndTime = $time1;

                for ($j = 0; $j < $n; $j++) {
                    $activityDuration = $faker->numberBetween(1, $diff / $n); // Asegúrese de que la duración no exceda el tiempo disponible
                    $startTime = (clone $lastEndTime)->addSeconds($faker->numberBetween(1, $diff - $activityDuration)); // Inicia después del final de la actividad anterior
                    $endTime = (clone $startTime)->addSeconds($activityDuration); // Termina después de la duración de la actividad

                    // Asegurarse de que la actividad termine antes de la siguiente asignación de lead
                    if ($endTime > $time2) {
                        $endTime = clone $time2;
                    }

                    // if more than 30 minutes, skip
                    if ($endTime->diffInMinutes($startTime) > 30) {
                        continue;
                    }

                    $answered = $faker->boolean(50);
                    $scheduleCallDatetime = $answered && $faker->boolean(50) ? $faker->dateTimeBetween(Carbon::now()->addDays(1), Carbon::now()->addDays(7)) : null;
                    $obs = $answered && $faker->boolean(50) ? $faker->text(100) : null;
                    $salesActivity = [
                        'user_id' => $user->id,
                        'lead_assignment_id' => $leadAssignment->id,
                        'lead_id' => $leadAssignment->lead_id,
                        'start' => $startTime,
                        'end' => $endTime,
                        'type' => 'Llamada',
                        'answered' => $answered,
                        'observation' => $obs,
                        'schedule_call_datetime' => $scheduleCallDatetime,
                        'created_at' => $startTime,
                        'updated_at' => $endTime,

                    ];

                    DB::table('sales_activities')->insert($salesActivity);

                    $lastEndTime = $endTime;

                    // Reducir el tiempo disponible para las siguientes actividades
                    $diff -= $startTime->diffInSeconds($lastEndTime);
                }
            }
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Get path param with name id
        $id = $request->route('id');
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
}
