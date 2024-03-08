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



    public function index2()
    {
        // max execution time
        ini_set('max_execution_time', -1);
        // Get unificacion_1.json from storage/app
        $json = file_get_contents(storage_path('app/unificacion_1.csv'));
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
        $data = $data->map(function ($item) {
            $courses = [];
            for ($i = 1; $i < 9; $i++) {




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

    // Capitaliza first letter and lower the rest of each word
    public function capitalize($string)
    {
        return ucwords(strtolower($string));
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
