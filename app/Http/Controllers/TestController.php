<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Http\Services\ImportStudentsService;
use App\Http\Services\ImportStudentsServiceSEG;
use App\Http\Services\LiveConnectService;
use App\Http\Services\ResendService;
use App\Http\Services\ZohoService;
use App\Jobs\GeneralJob;
use App\Models\Course;
use App\Models\Currency;
use App\Models\Due;
use App\Models\Extension;
use App\Models\Holiday;
use App\Models\LiveconnectMessagesLog;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\Price;
use App\Models\SapInstalation;
use App\Models\SapTry;
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

        $data = [];
        Student::with('orders.orderCourses.course')->get()->map(function ($student) {
            $student->orders->map(function ($order) {
                $order->orderCourses->map(function ($orderCourse) {
                    $orderCourse->course = $orderCourse->course;
                    return $orderCourse;
                });
                return $order;
            });
            return $student;
        })
            // csv with student.name, student.email, student.order[0].orderCourses.course.name, student.order[0].orderCourses.start, student.order[0].orderCourses.end, student.order[0].orderCourses.license
            ->map(function ($student) use (&$data) {
                return $student->orders->map(function ($order) use (&$data) {
                    return $order->orderCourses->map(function ($orderCourse) use ($order, &$data) {
                        $epa = [
                            'student_name' => $order->student->name,
                            'student_email' => $order->student->email,
                            'course_name' => $orderCourse->course->name,
                            'license' => $orderCourse->license,
                            'start' => $orderCourse->start,
                            'end' => $orderCourse->end,
                        ];
                        $data[] = $epa;
                    });
                });
                return $student;
            });

        // conver to csv
        $csv = '';
        $csv .= 'student_name,student_email,course_name,license,start,end' . "\n";
        foreach ($data as $key => $value) {
            $csv .= $value['student_name'] . ',' . $value['student_email'] . ',' . $value['course_name'] . ',' . $value['license'] . ',' . $value['start'] . ',' . $value['end'] . "\n";
        }
        return $csv;

        $extension = Extension::with('order.student', 'orderCourse.course')->first();
        $content = view("mails.extension")->with(['extension' => $extension])->render();

        return $content;
    }



    public function index2()
    {
        // max execution time
        ini_set('max_execution_time', -1);
        // Get unificacion_1.json from storage/app
        $json = file_get_contents(storage_path('app/unificacion-seguimiento.csv'));
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


        $data->slice(1)->values()->filter(function ($item) {
            return !Student::whereName($item['Nombre y apellido'])->exists();
        }, $data)->values();

        // Se remueven los headers
        $data = $data->slice(1)->values();





        // Se mapea la data
        $data = $data->map(function ($item) {
            $courses = [];
            $limit = 4;
            for ($i = 1; $i < $limit; $i++) {
                try {

                    $licencia = $item['licencia_' . $i] ? trim($item['licencia_' . $i]) : null;
                    if ($licencia != null) {
                        // to lower all
                        $licencia = strtolower($licencia);
                    }
                    $courses[] = [
                        'curso'        => $item['curso_' . $i] ? trim($item['curso_' . $i]) : null,
                        'estado'       => $item['estado_' . $i] ? trim($item['estado_' . $i]) : null,
                        'licencia'     => $licencia,
                        'fecha_inicio' => $item['fecha_inicio_' . $i] ? trim($item['fecha_inicio_' . $i]) : null,
                        'fecha_fin'    => $item['fecha_fin_' . $i] ? trim($item['fecha_fin_' . $i]) : null,
                    ];
                } catch (\Throwable $th) {
                }
            }
            $newData = [
                'name' => $item['Nombre y apellido'],
                'courses' => array_filter($courses, function ($course) {
                    return $course['curso'] != null;
                })
            ];
            return $newData;
        });

        $courses_ids = [
            'SAP PP' => 1,
            'SAP MM' => 2,
            'SAP PM' => 3,
            'SAP HCM' => 4,
            'SAP INTEGRAL' => 5,
            'SAP Integral' => 5,
            'EXCEL' => 6,
            'EXCEL EMPRESARIAL' => 6,
            'POWER BI' => 7,
            'PBI' => 7,
            'POWER BI AVANZADO' => 8,
            'POWER BI  AVANZADO ' => 8,
            'PBI AVANZADO' => 8,
            'MS PROJECT' => 9,
            'MSPROJECT' => 9,
            'MS  PROJECT' => 9,
            'SAP FI' => 10,
            'FI' => 10,

        ];


        // Se quitan todos los cursos que no tengan estado, o que tengan estado 'No aplica' o 'NO APLICA'
        $data = $data->map(function ($item) {
            $item['courses'] = array_filter($item['courses'], function ($course) {
                return $course['estado'] != null && $course['estado'] != 'No aplica' && $course['estado'] != 'NO APLICA';
            });
            return $item;
        });

        $data = $data->map(function ($item) use ($courses_ids) {
            $student = Student::whereName($item['name'])->with('orders.orderCourses')->first();

            if (!$student) {
                return $item;
            }

            // Se dividen los cursos si en la cadena de texto hay un '+', ya que significa que hay varios cursos con una misma licencia, fecha de inicio y fecha de fin
            $item['courses'] = array_reduce($item['courses'], function ($carry, $course) use ($courses_ids, $student) {
                if (strpos($course['curso'], '+') !== false) {
                    $courses = explode('+', $course['curso']);
                    foreach ($courses as $c_name) {
                        $carry[] = [
                            'curso'        => trim($c_name),
                            'estado'       => $course['estado'],
                            'licencia'     => $course['licencia'],
                            'fecha_inicio' => $course['fecha_inicio'],
                            'fecha_fin'    => $course['fecha_fin']
                        ];
                    }
                } else {
                    $carry[] = $course;
                }

                return $carry;
            }, []);


            // Find SAP QM and remove it
            $item['courses'] = array_filter($item['courses'], function ($course) {
                return $course['curso'] != 'SAP QM';
            });
            $item['courses'] = array_values($item['courses']);


            // Se mapean los cursos
            $item['courses'] = array_map(function ($course) use ($courses_ids, $student, $item) {

                if ($course['curso'] == 'SAP QM') {
                    return $course;
                }
                $course['curso'] = preg_replace('/\s+/', ' ', $course['curso']);

                if (!count($student->orders)) {

                    Order::create([
                        'student_id' => $student->id,
                        'payment_mode' => 'Contado',
                        'price_amount' => 0
                    ]);
                    $student = Student::whereName($item['name'])->with('orders.orderCourses')->first();
                }
                $orderCourse = $student->orders[0]->orderCourses->where('course_id', $courses_ids[$course['curso']])->first();
                $course['order_course'] = $orderCourse ? $orderCourse : null;
                return $course;
            }, $item['courses']);

            return $item;
        });



        $data = $data->map(function ($item) {
            $item['courses'] = collect($item['courses'])->map(function ($course) use ($item) {
                $start = $course['fecha_inicio'];
                $end = $course['fecha_fin'];

                if ($start == null || $end == null) {
                    return $course;
                }

                $dmyStart = Carbon::createFromFormat('d/m/Y', $start);
                $dmyEnd = Carbon::createFromFormat('d/m/Y', $end);

                $mdyStart = Carbon::createFromFormat('m/d/Y', $start);
                $mdyEnd = Carbon::createFromFormat('m/d/Y', $end);

                // if dmyStart is less than mdyStart and mdyEnd is less than dmyEnd then parse with dmy
                if ($dmyStart->lt($mdyStart) && $mdyEnd->lt($dmyEnd)) {
                    $course['fecha_inicio'] = $dmyStart->format('Y-m-d');
                    $course['fecha_fin'] = $dmyEnd->format('Y-m-d');
                    return $course;
                }

                // if mdyStart is less than dmyStart and dmyEnd is less than mdyEnd then parse with mdy
                if ($mdyStart->lt($dmyStart) && $dmyEnd->lt($mdyEnd)) {
                    $course['fecha_inicio'] = $mdyStart->format('Y-m-d');
                    $course['fecha_fin'] = $mdyEnd->format('Y-m-d');
                    return $course;
                }

                // if first part of start is graeter than 12 then parse with mdy
                if (explode('/', $start)[0] > 12 || explode('/', $end)[0] > 12) {
                    $course['fecha_inicio'] = $dmyStart->format('Y-m-d');
                    $course['fecha_fin'] = $dmyEnd->format('Y-m-d');
                    return $course;
                }

                if (explode('/', $start)[1] > 12 || explode('/', $end)[1] > 12) {
                    $course['fecha_inicio'] = $mdyStart->format('Y-m-d');
                    $course['fecha_fin'] = $mdyEnd->format('Y-m-d');
                    return $course;
                }

                // rounded month diff
                $diff1 = round($dmyStart->diffInMonths($dmyEnd) / 2);
                $diff2 = round($mdyStart->diffInMonths($mdyEnd) / 2);

                // if diff1 is less than diff2 then parse with dmy
                if ($diff1 > $diff2) {
                    $course['fecha_inicio'] = $dmyStart->format('Y-m-d');
                    $course['fecha_fin'] = $dmyEnd->format('Y-m-d');
                    return $course;
                }

                if ($diff1 < $diff2) {
                    $course['fecha_inicio'] = $mdyStart->format('Y-m-d');
                    $course['fecha_fin'] = $mdyEnd->format('Y-m-d');
                    return $course;
                }


                $course['fecha_inicio'] = $dmyStart->format('Y-m-d');
                $course['fecha_fin'] = $dmyEnd->format('Y-m-d');


                return $course;
            });
            return $item;
        });




        $holidays = Holiday::all();

        // // Para checkear los domingos y feriados
        // $data = $data->map(function ($item) use ($holidays) {
        //     $must_change = false;
        //     $item['courses'] = $item['courses']->map(function ($course) use ($holidays, &$must_change) {
        //         if ($course['fecha_inicio'] == null || $course['fecha_fin'] == null) {
        //             return $course;
        //         }

        //         // if fecha_fin already passed, return $course
        //         if (Carbon::parse($course['fecha_fin'])->lt(Carbon::now())) {
        //             return $course;
        //         }

        //         if (Carbon::parse($course['fecha_inicio'])->isSunday() || Carbon::parse($course['fecha_fin'])->isSunday() || $holidays->contains('date', $course['fecha_inicio']) || $holidays->contains('date', $course['fecha_fin'])) {
        //             $course['must_change'] = true;
        //             $must_change = true;
        //         }
        //         return $course;
        //     });
        //     $item['must_change'] = $must_change;
        //     return $item;
        // });

        // return $data->filter(function ($item) {
        //     return $item['must_change'];
        // })->values()->count();



        $data = $data->map(function ($item) use ($courses_ids) {
            $student = Student::whereName($item['name'])->with('orders.orderCourses')->first();

            if (!$student) {
                return $item;
            }


            $item['courses'] = array_map(function ($course) use ($courses_ids, $student) {

                // Remove all spaces
                $start = preg_replace('/\s+/', '', $course['fecha_inicio']);
                $end = preg_replace('/\s+/', '', $course['fecha_fin']);


                if ($course['order_course'] != null) {
                    // OrderCourse::find($course['order_course']->id)->update([

                    //     'classroom_status' =>  self::capitalize($course['estado']),
                    //     'license'          => $course['licencia'],
                    //     'start'            => $start ? $start : null,
                    //     'end'              => $end ? $end : null,
                    // ]);

                    // Update if start or end date is different
                    if ($course['order_course']->start != $start || $course['order_course']->end != $end) {
                        // Log::info('Start: ' . $start . ' End: ' . $end . ' Student: ' . $student->name . ' Course: ' . $course['curso']);
                        OrderCourse::find($course['order_course']->id)->update([
                            'start' => $start ? $start : null,
                            'end' => $end ? $end : null,
                        ]);
                    }
                } else {
                    OrderCourse::create([
                        'order_id'         => $student->orders[0]->id,
                        'course_id'        => $courses_ids[$course['curso']],
                        'type'             => Course::find($courses_ids[$course['curso']])->type,
                        'classroom_status' => self::capitalize($course['estado']),
                        'license'          => $course['licencia'],
                        'start'            => $start ? $start : null,
                        'end'              => $end ? $end : null,
                    ]);
                }

                return $course;
            }, $item['courses']->toArray());

            return $item;
        });



        return ['Exito' => $data];
    }


    public function index3()
    {

        $im = new ImportStudentsServiceSEG();
        return $im->index();
    }

    // Capitaliza first letter and lower the rest of each word
    public function capitalize($string)
    {
        return ucwords(strtolower($string));
    }

    public function excludeInvalidDays()
    {


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
        })
            ->values()->each(function ($order) use ($holidays) {



                // sort orderCourses by start date
                $orderCourses = $order->orderCourses->whereNotNull('start')->sortBy('start')->values();

                for ($i = 0; $i < $orderCourses->count(); $i++) {

                    $orderCourse = $orderCourses[$i];
                    $prevOrderCourse = $i > 0 ? $orderCourses[$i - 1] : null;

                    $start = Carbon::parse($orderCourse->start);
                    $end = Carbon::parse($orderCourse->end);


                    if (!$orderCourse->start || !$orderCourse->end) {
                        continue;
                    }

                    if ($prevOrderCourse && $orderCourses->count() != 5) {
                        $start = $start->addDays(1);
                    }

                    $start = Carbon::parse($start);

                    while ($holidays->contains('date', $start->format('Y-m-d')) || $start->isSunday()) {
                        $start = Carbon::parse($start)->addDays(1);
                    }



                    if ($orderCourses->count() > 1 && $orderCourses->count() < 5) {
                        $end = Carbon::parse($start)->addMonths(3);
                    }

                    if ($orderCourses->count() == 5) {
                        $end = Carbon::parse($start)->addMonths(12);
                    }

                    if ($orderCourses->count() == 1) {
                        $diffMonths = Carbon::parse($end)->diffInMonths($start);
                        $plusMonths = $diffMonths > 4 ? 6 : 3;
                        $end = Carbon::parse($start)->addMonths($plusMonths);
                    }


                    while ($holidays->contains('date', $end->format('Y-m-d')) || $end->isSunday()) {
                        $end->addDays(1);
                    }


                    $orderCourse->start = $start->format('Y-m-d');
                    $orderCourse->end = $end->format('Y-m-d');

                    // Remove startInfo and endInfo
                    unset($orderCourse->startInfo);
                    unset($orderCourse->endInfo);


                    $orderCourse->save();
                }
            });

        return "Exito";




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

            // prettier-ignore
            $due = Due::create([
                'date'                => $instalation->payment_date,
                'amount'              => $instalation->price_amount,
                'payment_method_id'   => NULL,
                'currency_id'         => $currency_id,
                'price_id'            => $instalation->price_id,
                'payment_receipt'     => $instalation->payment_receipt,
                'payment_verified_at' => $instalation->payment_verified_at,
                'payment_verified_by' => $instalation->payment_verified_by,
                'payment_reason'      => $instalation->instalation_type == 'Desbloqueo SAP' ? 'Desbloqueo SAP' : 'Instalación SAP',
                'student_id'          => Order::withTrashed()->where('id', $instalation->order_id)->first()->student_id,
            ]);

            SapInstalation::where('id', $instalation->id)->update(['due_id' => $due->id]);
        });

        return 'Exito';
    }
}
