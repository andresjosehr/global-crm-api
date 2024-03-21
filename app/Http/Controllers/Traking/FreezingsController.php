<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\DuesController;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Controllers\NotificationController;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Jobs\GeneralJob;
use App\Models\DatesHistory;
use App\Models\Due;
use App\Models\Freezing;
use App\Models\Holiday;
use App\Models\OrderCourse;
use App\Models\Process;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FreezingsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $freezing = new Freezing();
        $freezing->order_id = $request->order_id;

        $orderCourses = OrderCourse::where('order_id', $request->order_id)->where('type', 'paid')->get();

        $freezing->courses = $orderCourses->count() == 5 ? 'all' : 'single';

        $orderCoursesSync = $freezing->courses == 'all' ? $orderCourses->pluck('id') : collect([$request->order_course_id]);
        $orderCoursesSync = $orderCoursesSync->mapWithKeys(function ($item) use ($request) {
            return [$item => ['order_id' => $request->order_id]];
        });


        $freezing->save();

        $freezing->orderCourses()->sync($orderCoursesSync);

        $freezing = Freezing::where('id', $freezing->id)->first();

        return ApiResponseController::response('Freezing saved', 200, $freezing);
    }


    public function update(Request $request, $id)
    {

        $datesFields = ['start_date', 'finish_date', 'return_date', 'payment_date', 'new_return_date', 'new_finish_date'];

        // remove fue_id
        $data = $request->all();
        unset($data['due_id']);

        $fillable = (new Freezing())->getFillable();
        $free     = array_filter($data, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        foreach ($datesFields as $date) {
            if (isset($free[$date])) {
                $free[$date] = Carbon::parse($free[$date])->format('Y-m-d');
            }
        }


        Freezing::where('id', $id)->update($free);
        $freezingDB = Freezing::where('id', $id)->first();

        $orderCoursesSync = $freezingDB->courses == 'all' ? collect($request->order_course_ids) : collect([$request->order_course_id]);
        $orderCoursesSync = $orderCoursesSync->mapWithKeys(function ($item) use ($request) {
            return [$item => ['order_id' => $request->order_id]];
        });

        $freezingDB->orderCourses()->sync($orderCoursesSync);

        $freezingDB = Freezing::where('id', $id)->first();




        if (!$freezingDB->due_id && $request->due_id == 1) {
            $due = Due::create([
                'payment_reason' => 'Congelación',
                'student_id' => $freezingDB->orderCourses[0]->order->student_id,
            ]);

            $freezingDB->due_id = $due->id;
            $freezingDB->save();

            $noti = new NotificationController();
            $noti = $noti->store([
                'title'      => 'Se ha registrado un pago de un congelamiento',
                'body'       => 'Se ha registrado un pago de congelamiento del alumno ' . $freezingDB->orderCourses[0]->order->student->name . ' Por favor revisar el pago',
                'icon'       => 'check_circle_outline',
                'url'        => '#',
                'user_id'    => 10,
                'use_router' => false,
            ]);
        }

        if ($freezingDB->due_id && $freezingDB->due_id != 1) {
            $dueController = new DuesController();
            $dueController->updatePayment($request, $freezingDB->due_id);
        }

        $freezingDB = Freezing::where('id', $id)->first();
        // Check if start_date and return_date exists
        $dateHistory = DatesHistory::where('freezing_id', $id)->first();

        if ($freezingDB->due_id == null && !$freezingDB->set) {
            if ($freezingDB->courses == 'single') {
                self::setFreezingSingle($freezingDB);
            } else {
                self::setFreezingMany($freezingDB);
            }
        }



        $freezing = Freezing::with('orderCourses', 'due')->where('id', $id)->first();


        return ApiResponseController::response('Exito', 200, ['freezing' => $freezing]);
    }





    static public function setFreezingSingle($freezing)
    {
        $orderCourse = OrderCourse::where('id', $freezing->order_course_id)->first();
        DatesHistory::create([
            'order_id'        => $orderCourse->order_id,
            'order_course_id' => $freezing->order_course_id,
            'start_date'      => $orderCourse->start,
            'end_date'        => $freezing->finish_date,
            'freezing_id'     => $freezing->id,
            'type'            => 'Congelamiento',
        ]);


        // Get date_history created
        $dateHistory = DatesHistory::where('freezing_id', $freezing->id)->first();

        OrderCourse::where('id', $freezing->order_course_id)->update([
            'end' => $dateHistory->end_date
        ]);

        self::sendFreezeMail($freezing, $freezing->order_course_id);

        $now = Carbon::now();
        if ($now->between(Carbon::parse($freezing->start_date), Carbon::parse($freezing->finish_date))) {
            OrderCourse::where('id', $freezing->order_course_id)->update(['classroom_status' => 'Congelado']);
        }

        if ($freezing) {
            $freeDB = Freezing::where('id', $freezing->id)->first();
            self::moveNextCoursesDate($freeDB);
        }

        $currentDate = Carbon::now();
        $startDate   = Carbon::parse($freezing->start_date);
        $returnDate  = Carbon::parse($freezing->return_date);

        if ($currentDate->between($startDate, $returnDate)) {
            OrderCourse::where('id', $freezing->order_course_id)->update(['classroom_status' => 'Congelado']);
        }

        $freezing->set = true;
        $freezing->save();
    }

    static public function setFreezingMany($freezing)
    {
        $orderCourses = $freezing->orderCourses()->get();

        foreach ($orderCourses as $orderCourse) {
            DatesHistory::create([
                'order_id'        => $orderCourse->order_id,
                'order_course_id' => $orderCourse->id,
                'start_date'      => $orderCourse->start,
                'end_date'        => $freezing->finish_date,
                'freezing_id'     => $freezing->id,
                'type'            => 'Congelamiento',
            ]);

            // Get date_history created
            $dateHistory = DatesHistory::where('freezing_id', $freezing->id)->first();

            OrderCourse::where('id', $orderCourse->id)->update([
                'end' => $dateHistory->end_date
            ]);

            self::sendFreezeMail($freezing, $orderCourse->id);

            $now = Carbon::now();
            if ($now->between(Carbon::parse($freezing->start_date), Carbon::parse($freezing->finish_date))) {
                OrderCourse::where('id', $orderCourse->id)->update(['classroom_status' => 'Congelado']);
            }

            if ($freezing) {
                $freeDB = Freezing::where('id', $freezing->id)->first();
                self::moveNextCoursesDate($freeDB);
            }

            $currentDate = Carbon::now();
            $startDate   = Carbon::parse($freezing->start_date);
            $returnDate  = Carbon::parse($freezing->return_date);

            if ($currentDate->between($startDate, $returnDate)) {
                OrderCourse::where('id', $orderCourse->id)->update(['classroom_status' => 'Congelado']);
            }

            $freezing->set = true;
            $freezing->save();
        }
    }



    public function unfreezeCourse($order_course_id)
    {
        $o = OrderCourse::with(['freezings' => function ($query) {
            $query->orderBy('id', 'desc')->first();
        }])
            ->where('id', $order_course_id)
            ->first();

        $lastFreezing = $o->freezings->first();
        $orderCourses = $lastFreezing->orderCourses()->get();

        if (!$lastFreezing) {
            return ApiResponseController::response('No hay congelamientos', 422);
        }

        $now = Carbon::now();
        if (!$now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            return ApiResponseController::response('El curso no esta congelado', 422);
        }


        // Update return_date
        $newReturnDate = Carbon::now();
        $newFinishDate = Carbon::parse($lastFreezing->finish_date);
        $diff = $newReturnDate->diffInDays(Carbon::parse($lastFreezing->return_date));
        $newFinishDate->subDays($diff);

        Freezing::where('id', $lastFreezing->id)->update(['new_return_date' => $newReturnDate, 'new_finish_date' => $newFinishDate]);

        foreach ($orderCourses as $orderCourse) {
            $orderCourse->update(['classroom_status' => 'Cursando', 'end' => $newFinishDate]);
            DatesHistory::create([
                'order_course_id' => $order_course_id,
                'start_date'      => $orderCourse->start,
                'end_date'        => $newFinishDate,
                'type'            => 'Descongelamiento',
                'freezing_id'     => $lastFreezing->id
            ]);
            self::sendUnfreezingEmail($lastFreezing, $orderCourse);
        }


        $last_freezing = Freezing::where('id', $lastFreezing->id)
            ->with('orderCourses.course')
            ->orderBy('id', 'desc')->first();



        return ApiResponseController::response('Exito', 200, $last_freezing);
    }

    static public function moveNextCoursesDate(Freezing $freezing, $to = 'forward', $coursesNumber = 'single')
    {
        $finish_date = $to == 'forward' ? $freezing->finish_date : $freezing->new_finish_date;
        $orderCourse = $freezing->orderCourses()->first();
        $nextCourses = OrderCourse::where('order_id', $orderCourse->order_id)->where('start', '>', $orderCourse->start)->where('type', $orderCourse->type)->orderBy('start', 'asc')->get();
        if (count($nextCourses) == 0) {
            return;
        }

        if ($to == 'backward') {
            OrderCourse::where('id', $freezing->order_course_id)->update(['end' => $finish_date]);
        }

        $finish_date = Carbon::parse($finish_date);
        foreach ($nextCourses as $order_course) {
            $newStartDate = Carbon::parse($finish_date)->addDays(1);

            $holidays = Holiday::all();
            // if new start date is a holiday or sunday, add one day
            while ($holidays->contains('date', $newStartDate->format('Y-m-d')) || $newStartDate->dayOfWeek == 0) {
                $newStartDate->addDays(1);
            }

            $newEndDate = Carbon::parse($newStartDate)->addMonths(3);

            while ($holidays->contains('date', $newEndDate->format('Y-m-d')) || $newEndDate->dayOfWeek == 0) {
                $newEndDate->addDays(1);
            }

            $finish_date = $newEndDate;
            OrderCourse::where('id', $order_course->id)->update(['start' => $newStartDate, 'end' => $newEndDate]);
            DatesHistory::create([
                'order_course_id' => $order_course->id,
                'start_date'      => $newStartDate,
                'end_date'        => $newEndDate,
                'type'            => $to == 'forward' ? 'Congelamiento de un curso anterior a este' : 'Descongelamiento de un curso anterior a este',
            ]);
        }


        return true;
    }




    static public function sendFreezeMail($freezing, $order_course_id)
    {
        // Get user


        $order_course  = OrderCourse::where('id', $order_course_id)->with('dateHistory', 'course', 'order.student', 'freezings')->first();
        $student       = $order_course->order->student;
        $dates_history = $order_course->dateHistory;
        $course        = $order_course->course;
        $remainFreezingDurationAvaliable = 3 - $order_course->freezings->reduce(function ($carry, $item) {
            return $carry + $item->months;
        }, 0);

        // Get date history record before the freezing by id
        $dateRecord = DatesHistory::where('order_course_id', $order_course_id)->get();
        // Get index of the date history record before the freezing
        $index = $dateRecord->search(function ($item) use ($freezing) {
            return $item->freezing_id == $freezing->id;
        }) - 1;
        if (isset($dateRecord[$index])) {
            $original_date = $dateRecord[$index];
        } else {
            $original_date = OrderCourse::where('id', $order_course_id)->first();
        }


        $content = view('mails.freezing')->with([
            'order_course'                    => $order_course,
            'student'                         => $student,
            'dates_history'                   => $dates_history,
            'original_date'                   => $original_date,
            'course'                          => $course,
            'freezing'                        => $freezing,
            'remainFreezingDurationAvaliable' => $remainFreezingDurationAvaliable
        ])->render();

        Freezing::where('id', $freezing->id)->update(['mail_status' => 'Enviado']);

        $mail = [[
            'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
            'to'         => [$student->email],
            'subject'    => 'Tomalo con calma, ¡Congelamos tu curso!',
            'student_id' => $student->id,
            'html'    => $content
        ]];



        ResendService::sendBatchMail($mail);

        GeneralJob::dispatch(FreezingsController::class, 'sendLiveConnectMessage', [
            'phone'      => $student->phone,
            'message'    => "Le he enviado el correo con la información sobre su curso y sus nuevas fechas de inicio, si tiene dudas por favor me contacta por este medio.\n Le recuerdo que debe *mantener su conexión a SAP y anotar su contraseña* para no olvidarla, *ya que no mantenemos el respaldo de la misma.*",
            'student_id' => $student->id,
            'type'       => 'FREEZING_NOTIFICATION'
        ])->onQueue('liveconnect');
    }

    public function sendLiveConnectMessage($phone, $message, $student_id, $type)
    {
        $liveconnectService = new LiveConnectService();
        $liveconnectService->sendMessage(521, $phone, $message, $student_id, 'SCHEDULED', $type, 1);
        sleep(rand(12, 20));
    }

    public function sendUnfreezingEmail($freezing, $orderCourse)
    {
        $mail = [[
            'from'       => 'No contestar <noreply@globaltecnoacademy.com>',
            'to'         => [$freezing->orderCourses[0]->order->student->email],
            'subject'    => 'Continúa con tu Capacitación de ' . $orderCourse->course->name . ' con ¡Global Tecnologías Academy!',
            'student_id' => $orderCourse->order->student->id,
            'html'    => view('mails.unfreezing_new')->with(['freezing' => $freezing, 'orderCourse' => $orderCourse])->render()
        ]];

        ResendService::sendBatchMail($mail);
    }

    public function dispatchMail($email, $subject, $content, $scheduleTime, $freezing_id)
    {
        $message = CoreMailsController::sendMail($email, $subject, $content, $scheduleTime);
        Freezing::where('id', $freezing_id)->update(['mail_id' => $message->messageId]);
    }


    public function importFreezings(Request $request)
    {
        // max execution time
        ini_set('max_execution_time', -1);
        // Get unificacion_1.json from storage/app
        $json = file_get_contents(storage_path('app/congelados.csv'));
        $json = explode("\n", $json);
        foreach ($json as $key => $value) {
            $json[$key] = explode(",", $value);
        }

        // set headers as keys
        $headers = collect($json[0]);
        $data = collect($json)->map(function ($row) use ($headers) {
            return collect($row)->mapWithKeys(function ($item, $key) use ($headers) {
                return [$headers[$key] => $item];
            });
        });

        //  remove first row
        $data->shift();

        $data = $data->filter(function ($row) {
            return isset($row['CURSO']) && $row['NOMBRE COMPLETO CLIENTE'];
        })->values();

        $data = $data->map(function ($row) {
            $row['freezing'] = collect([]);

            $row['name'] = $row['NOMBRE COMPLETO CLIENTE'];
            unset($row['NOMBRE COMPLETO CLIENTE']);
            unset($row['FECHA DE INICIO']);
            // unset($row['FECHA DE FIN']);

            unset($row['TIEMPO DISPONIBLE PARA VOLVER A CONGELAR ']);
            unset($row['']);

            if (!isset($row['CONGELACIÓN 1'])) {
                return $row;
            }

            $row['courses'] = collect(explode('+', $row['CURSO']));
            $row['courses'] = $row['courses']->map(function ($course) {
                return trim($course);
            });

            $row['order_courses_ids'] = $row['courses']->map(function ($course) use ($row) {
                return OrderCourse::with('student')->whereHas('student', function ($query) use ($row) {
                    $query->where('name', 'LIKE', '%' . $row['name'] . '%');
                })->whereHas('course', function ($query) use ($row) {
                    return $query->whereIn('short_name', $row['courses']);
                })->get()->pluck('id');
            })->unique()->flatten();

            unset($row['CURSO']);


            for ($i = 1; $i < 6; $i++) {


                if ($row['CONGELACIÓN ' . $i]  && $row['FECHA DE INICIO ' . $i] && $row['FECHA DE FIN ' . $i]) {

                    $row['freezing']->push([
                        'months'      => $row['CONGELACIÓN ' . $i],
                        'start'       => $row['FECHA DE INICIO ' . $i],
                        'return_date' => $row['FECHA DE FIN ' . $i],
                    ]);

                    Log::info($row['FECHA DE FIN']);
                    $free = Freezing::create([
                        'months'      => explode(' ', $row['CONGELACIÓN ' . $i])[0],
                        'start_date'  => Carbon::createFromFormat('m/d/Y', $row['FECHA DE INICIO ' . $i]),
                        'return_date' => Carbon::createFromFormat('m/d/Y', $row['FECHA DE FIN ' . $i]),
                        'remain_license' => $row['LICENCIA DISPONIBLE ACTUAL'],
                        'finish_date' => Carbon::createFromFormat('m/d/Y', trim($row['FECHA DE FIN'])),
                    ]);

                    $free->orderCourses()->sync($row['order_courses_ids']->toArray());
                    // Update order curse to "Congelado"
                    $row['order_courses_ids']->each(function ($order_course_id) use ($free) {
                        $return_date = Carbon::parse($free->return_date);
                        if ($return_date->isPast()) {
                            OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Cursando']);
                        } else {
                            OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Congelado']);
                        }
                    });
                }

                unset($row['CONGELACIÓN ' . $i]);
                unset($row['FECHA DE INICIO ' . $i]);
                unset($row['FECHA DE FIN ' . $i]);
            }
            return $row;
        });

        return ["Exito" => $data];
    }

    public function importUnfreezings(Request $request)
    {
        // max execution time
        ini_set('max_execution_time', -1);
        // Get unificacion_1.json from storage/app
        $json = file_get_contents(storage_path('app/descongelados.csv'));
        $json = explode("\n", $json);
        foreach ($json as $key => $value) {
            $json[$key] = explode(",", $value);
        }


        // set headers as keys
        $headers = collect($json[0]);
        $data = collect($json)->map(function ($row) use ($headers) {
            return collect($row)->mapWithKeys(function ($item, $key) use ($headers) {
                return [$headers[$key] => $item];
            });
        });

        //  remove first row
        $data->shift();

        $data = $data->filter(function ($row) {
            return isset($row['CURSO']) && $row['NOMBRE COMPLETO CLIENTE'];
        });

        $data = $data->map(function ($row) {
            $row['courses'] = collect(explode('+', $row['CURSO']))->map(function ($course) {
                return trim($course);
            });
            return $row;
        });

        $data = $data->map(function ($row) {
            try {
                $row['order_course_ids'] = OrderCourse::with('student')->whereHas('student', function ($query) use ($row) {
                    $query->where('name', 'LIKE', '%' . $row['NOMBRE COMPLETO CLIENTE'] . '%');
                })->whereHas('course', function ($query) use ($row) {
                    return $query->whereIn('short_name', $row['courses']);
                })->get()->pluck('id');
            } catch (\Exception $e) {
            }
            return $row;
        });

        $data->each(function ($row) {
            Log::info($row);
            $freezing = [
                'start_date'     => '2021-01-01',
                'months'         => 3 - explode(' ', $row['TIEMPO DISPONIBLE PARA CONGELAR'])[0],
                'remain_license' => $row['TIEMPO LICENCIA'],
            ];

            $freezing = Freezing::create($freezing);
            $freezing->orderCourses()->sync($row['order_course_ids']->toArray());
        });



        return ["Exito" => $data];
    }
}
