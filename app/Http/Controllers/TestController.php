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
        // execution time
        ini_set('max_execution_time', -1);

        $asesores = [
            '14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs' => User::whereName('MC')->first()->id,
            '1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo' => User::whereName('MS')->first()->id,
            '1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI' => User::whereName('GD')->first()->id,
            '15IgSGsDjfrJMLaVRwkpxkusiyNHc0nSaFRpuRJ1ywWk' => User::whereName('LJ')->first()->id,

            '1blqfsvTck5sWfFd1AMLEt732Bo395R1GuFFZuuOk2Lk' => User::whereName('MR')->first()->id,
            '1O46poG06FfnCCTuTNUuFr4NGbbzYstFICNCiGPezBpg' => User::whereName('JH')->first()->id,
        ];

        $googleSheet = new GoogleSheetController();

        $sheets = DB::table('sheets')
            ->whereNot('sheet_id', '17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA')
            ->where('type', 'prod')
            ->get()->toArray();



        // add aditional sheets
        $sheets[] = (object) [
            'sheet_id'      => '1blqfsvTck5sWfFd1AMLEt732Bo395R1GuFFZuuOk2Lk',
            'course_tab_id' => 1487249389,
        ];

        $sheets[] = (object) [
            'sheet_id'      => '1O46poG06FfnCCTuTNUuFr4NGbbzYstFICNCiGPezBpg',
            'course_tab_id' => 1552535677,
        ];

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



    public function index2($type)
    {
        // max execution time
        ini_set('max_execution_time', -1);
        // Get unificacion_1.json from storage/app
        $json = file_get_contents(storage_path('app/unificacion_' . $type . '.csv'));
        $json = explode("\n", $json);
        foreach ($json as $key => $value) {
            $json[$key] = explode(";", $value);
        }

        // set headers as keys
        $headers = collect($json[0]);
        $data = collect($json)->map(function ($row) use ($headers) {
            return collect($row)->mapWithKeys(function ($item, $key) use ($headers) {
                return [$headers[$key] => $item];
            });
        });


        // remove headers
        $data->slice(1)->values()->filter(function ($item) {
            return Student::whereName($item['Nombre y apellido'])->exists();
        }, $data)->values();

        // Se remueven los headers
        $data = $data->slice(1)->values();


        // Se mapea la data
        $data = $data->map(function ($item) use ($type) {
            $courses = [];
            $limit = $type == 1 ? 10 : 4;
            for ($i = 1; $i < $limit; $i++) {




                try {

                    $licencia = $item['licencia_' . $i] ? trim($item['licencia_' . $i]) : null;
                    if ($licencia != null) {
                        $licencia = explode(' ', $licencia)[0] . ' Meses';
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
                // Log::info($student);
                $orderCourse = $student->orders[0]->orderCourses->where('course_id', $courses_ids[$course['curso']])->first();
                $course['order_course'] = $orderCourse ? $orderCourse : null;
                return $course;
            }, $item['courses']);

            return $item;
        });



        // return $data;
        $data = $data->map(function ($item) use ($courses_ids) {
            $student = Student::whereName($item['name'])->with('orders.orderCourses')->first();

            if (!$student) {
                return $item;
            }


            $item['courses'] = array_map(function ($course) use ($courses_ids, $student) {

                // Remove all spaces
                $start = preg_replace('/\s+/', '', $course['fecha_inicio']);
                $end = preg_replace('/\s+/', '', $course['fecha_fin']);

                Log::info($end);
                if ($course['order_course'] != null) {
                    OrderCourse::find($course['order_course']->id)->update([

                        'classroom_status' =>  self::capitalize($course['estado']),
                        'license'          => strtolower($course['licencia']),
                        'start'            => $start ? Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d') : null,
                        'end'              => $end ? Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d') : null,
                    ]);
                } else {
                    OrderCourse::create([
                        'order_id'         => $student->orders[0]->id,
                        'course_id'        => $courses_ids[$course['curso']],
                        'type'             => Course::find($courses_ids[$course['curso']])->type,
                        'classroom_status' => self::capitalize($course['estado']),
                        'license'          => strtolower($course['licencia']),
                        'start'            => $start ? Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d') : null,
                        'end'              => $end ? Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d') : null,
                    ]);
                }

                return $course;
            }, $item['courses']);

            return $item;
        });



        return ['Exito' => $data];
    }


    public function index3()
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

    // Capitaliza first letter and lower the rest of each word
    public function capitalize($string)
    {
        return ucwords(strtolower($string));
    }

    public function excludeInvalidDays()
    {


        $holidays = Holiday::all();

        Order::with('orderCourses', 'student')->get()->map(function ($order) use ($holidays) {

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
}
