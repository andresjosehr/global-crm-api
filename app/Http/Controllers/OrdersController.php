<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\CertificationTest;
use App\Models\Course;
use App\Models\Currency;
use App\Models\DatesHistory;
use App\Models\DocumentType;
use App\Models\Message;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Models\Price;
use App\Models\Student;
use App\Models\UserActivity;
use Carbon\Carbon;

class OrdersController extends Controller
{

    public $months = [
        '1 mes'    => 1,
        '2 meses'  => 2,
        '3 meses'  => 3,
        '4 meses'  => 4,
        '5 meses'  => 5,
        '6 meses'  => 6,
        '7 meses'  => 7,
        '8 meses'  => 8,
        '9 meses'  => 9,
        '10 meses' => 10,
        '11 meses' => 11,
        '12 meses' => 12,
    ];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request)
    {

        // return $request->all();
        // Get user
        $user = $request->user();
        $id = $user->id;

        $student = Student::find($request->student_id);
        UserActivity::create([
            'user_id'     => $user->id,
            'description' => 'Creó una orden para el estudiante ' . $student->name . ' el ' . Carbon::now()->format('d/m/Y H:i:s')
        ]);

        $order = new \App\Models\Order();


        $order->student_id        = $request->student_id;
        $order->currency_id       = $request->currency_id;
        $order->enrollment_sheet  = $request->enrollment_sheet;
        $order->payment_mode      = $request->payment_mode;
        $order->price_id          = $request->price_id;
        $order->free_courses_date = $request->free_courses_date;
        $order->price_amount      = $request->price_amount;
        $order->created_by        = $id;
        $order->comunication_type = $request->comunication_type;
        $order->observations      = $request->observations;

        // Generate random key
        $order->key = md5(microtime());


        $order->save();

        $orderCourses = $request->order_courses;  // Copiar el valor a una variable
        if ($request->free_courses_date == 'Misma fecha de curso SAP') {
            // Get minor date in paid courses
            $paidCourses = array_values(array_filter($request->order_courses, function ($item) {
                return $item['type'] == 'paid';
            }));

            // Get less start date
            $lessStartDate = array_reduce($paidCourses, function ($carry, $item) {
                if ($carry == null) {
                    return $item['start'];
                } else {
                    return $item['start'] < $carry ? $item['start'] : $carry;
                }
            });



            $i = 0;
            foreach ($orderCourses as $orderCourse) {
                if ($orderCourse['type'] == 'free') {
                    $orderCourses[$i]['start'] = $lessStartDate;
                    $orderCourses[$i]['end'] = Carbon::parse($lessStartDate)->addMonths(3)->format('Y-m-d');
                    // Check if end date is sunday, if true, add one day
                    if (Carbon::parse($orderCourses[$i]['end'])->dayOfWeek == 0) {
                        $orderCourses[$i]['end'] = Carbon::parse($orderCourses[$i]['end'])->addDay()->format('Y-m-d');
                    }
                }
                $i++;
            }
        }

        if ($request->free_courses_date == 'Delegar al area academica') {
            $i = 0;
            foreach ($orderCourses as $orderCourse) {
                if ($orderCourse['type'] == 'free') {
                    $orderCourses[$i]['start'] = null;
                    $orderCourses[$i]['end'] = null;
                }
                $i++;
            }
        }


        foreach ($orderCourses as $orderCourse) {

            $now = Carbon::now();
            if ($now->between(Carbon::parse($orderCourse['start']), Carbon::parse($orderCourse['end']))) {
                $orderCourse['classroom_status'] = 'Cursando';
            }
            if ($now->gt(Carbon::parse($orderCourse['end']))) {
                $orderCourse['classroom_status'] = 'No culminado';
            }
            if ($now->lt(Carbon::parse($orderCourse['start']))) {
                $orderCourse['classroom_status'] = 'Por habilitar';
            }

            $oc = $order->orderCourses()->create($orderCourse);
            DatesHistory::create([
                'order_course_id' => $oc->id,
                'order_id'        => $order->id,
                'start_date'      => $orderCourse['start'],
                'end_date'        => $orderCourse['end'],
                'type'            => 'Primero'
            ]);
        }


        $dues = array_map(function ($item) use ($order) {
            $item['currency_id'] = $order->currency_id;
            $item['student_id'] = $order->student_id;
            $item['payment_reason'] = 'Curso';
            $item['currency_id'] = $order->currency_id;
            return $item;
        }, $request->dues);

        // Dues
        $order->dues()->createMany($dues);


        $freeCourses = [6, 7, 8, 9];

        foreach ($order->orderCourses as $course) {
            $premium = true;

            $course_id = $course->course_id;
            $limit = array_search($course_id, $freeCourses) ? 4 : 6;

            if ($course_id != 6) {
                for ($i = 0; $i < $limit; $i++) {
                    if ($i < $limit - 1) {
                        $name = "Examen de certificación " . ($i + 1);
                        $premium = array_search($course_id, $freeCourses) ? false : $i >= 3;
                    } else {
                        $name = "Ponderación";
                        $premium = true;
                    }

                    $certificationTest = new CertificationTest();
                    $certificationTest->description = $name;
                    $certificationTest->order_id = $order->id;
                    $certificationTest->order_course_id = $course->id;
                    $certificationTest->enabled = $i < 3;
                    $certificationTest->status = 'Sin realizar';
                    $certificationTest->premium = $premium;
                    $certificationTest->save();
                }
            }

            if ($course_id == 6) {
                $cert = ['BASICO', 'INTERMEDIO', 'AVANZADO'];
                foreach ($cert as $c) {
                    for ($i = 0; $i < 4; $i++) {
                        $name = $i < 3 ? $c . " " . ($i + 1) : "Ponderación " . $c;
                        $premium = $i < 3 ? false : true;
                        $certificationTest = new CertificationTest();
                        $certificationTest->description = $name;
                        $certificationTest->order_id = $order->id;
                        $certificationTest->order_course_id = $course->id;
                        $certificationTest->enabled = true;
                        $certificationTest->status = 'Sin realizar';
                        $certificationTest->premium = $premium;
                        $certificationTest->save();
                    }
                }
            }
        }


        // $invoice = new Invoice();
        // if ($request->invoice['tax_situation_proof_changed']) {
        //     // Convert base64 to pdf
        //     $file = $request->invoice['tax_situation_proof'];
        //     $file = str_replace('data:application/pdf;base64,', '', $file);
        //     $file = str_replace(' ', '+', $file);
        //     $fileName = 'tax_situation_proof_' . $order->id . '.pdf';
        //     \File::put(storage_path() . '/app/public/invoices/' . $fileName, base64_decode($file));
        //     $invoice->tax_situation_proof = $fileName;
        // }

        // $invoice->requested     = $request->invoice['requested'];
        // $invoice->ruc           = $request->invoice['ruc'];
        // $invoice->business_name = $request->invoice['business_name'];
        // $invoice->email         = $request->invoice['email'];
        // $invoice->tax_situation = $request->invoice['tax_situation'];
        // $invoice->tax_regime    = $request->invoice['tax_regime'];
        // $invoice->address       = $request->invoice['address'];
        // $invoice->postal_code   = $request->invoice['postal_code'];
        // $invoice->cellphone     = $request->invoice['cellphone'];
        // $invoice->cfdi_use      = $request->invoice['cfdi_use'];
        // $invoice->type          = $request->invoice['type'];
        // $invoice->order_id      = $order->id;

        // $invoice->save();


        // Get id
        $order = Order::with('orderCourses.course', 'orderCourses.certificationTests', 'orderCourses.freezings.due', 'sapInstalations.due', 'orderCourses.dateHistory', 'dues', 'student', 'currency', 'price')->find($order->id);

        $params = [
            'order' => $order,
        ];

        // GeneralJob::dispatch(OrdersController::class, 'dispatchWelcomeMails', $params)->onQueue('default');

        return ApiResponseController::response('Orden creada exitosamente', 201, $order);
    }


    public function dispatchWelcomeMails(Order $order)
    {
        $emails = [
            1  => 'sap-welcome',
            2  => 'sap-welcome',
            3  => 'sap-welcome',
            4  => 'sap-welcome',
            5  => 'integral-welcome',
            6  => 'excel-welcome',
            7  => 'powerbi-welcome',
            8  => 'powerbi-welcome',
            9  => 'msproject-welcome',
            10 => 'sap-welcome',
        ];

        foreach ($order->orderCourses as $orderCourse) {
            $id = $orderCourse->course->id;
            $content = view("mails." . $emails[$id])->with([
                'orderCourse' => $orderCourse
            ])->render();

            $scheduleTime = Carbon::parse($orderCourse->start)->format('m/d/Y');
            $subject = '-Bienvenido(a) a tu curso de ' . $orderCourse->course->name . ' ¡Global Tecnologías Academy!';

            $message = CoreMailsController::sendMail($order->student->email, $subject, $content, $scheduleTime);

            OrderCourse::where('id', $orderCourse->id)->update(['welcome_mail_id' => $message->id]);
        }
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $order = Order::with('orderCourses.course', 'orderCourses.extensions', 'orderCourses.certificationTests.due', 'orderCourses', 'orderCourses.freezings.due', 'orderCourses.dateHistory', 'sapInstalations.staff', 'sapInstalations.lastSapTry', 'sapInstalations.due', 'sapInstalations.student', 'sapInstalations.due', 'currency', 'dues', 'createdBy', 'invoice')->find($id);
        if (!$order) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        return ApiResponseController::response('Consulta exitosa', 200, $order);
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
        if (Order::where('id', $id)->exists()) {
            $order = Order::find($id);

            $order->student_id                 = $request->student_id;
            $order->currency_id                = $request->currency_id;
            $order->enrollment_sheet           = $request->enrollment_sheet;
            $order->comunication_type          = $request->comunication_type;
            // $order->payment_mode               = $request->payment_mode;
            // $order->price_id                   = $request->price_id;
            // $order->price_amount               = $request->price_amount;
            $order->terms_confirmed_by_student = $request->terms_confirmed_by_student;
            $order->observations               = $request->observations;
            $order->free_courses_date          = $request->free_courses_date;
            $order->price_amount               = $request->price_amount;

            $order->save();

            // Order Courses
            $this->syncRelation($order->orderCourses(), $request->order_courses);

            // Dues
            $this->syncRelation($order->dues(), $request->dues);


            // $invoice = Invoice::where('order_id', $order->id)->first();

            // if ($request->invoice['tax_situation_proof_changed']) {
            //     $file = $request->invoice['tax_situation_proof'];
            //     $file = str_replace('data:application/pdf;base64,', '', $file);
            //     $file = str_replace(' ', '+', $file);
            //     $date = date('Y-m-d-H-i-s');
            //     $fileName = explode('.', $request->invoice['tax_situation_proof_name'])[0] . '_' . $date . '.pdf';
            //     \File::put(storage_path() . '/app/public/invoices/' . $fileName, base64_decode($file));
            //     $invoice->tax_situation_proof = $fileName;
            // }

            // $invoice->requested     = $request->invoice['requested'];
            // $invoice->ruc           = $request->invoice['ruc'];
            // $invoice->business_name = $request->invoice['business_name'];
            // $invoice->email         = $request->invoice['email'];
            // $invoice->tax_situation = $request->invoice['tax_situation'];
            // $invoice->tax_regime    = $request->invoice['tax_regime'];
            // $invoice->address       = $request->invoice['address'];
            // $invoice->postal_code   = $request->invoice['postal_code'];
            // $invoice->cellphone     = $request->invoice['cellphone'];
            // $invoice->cfdi_use      = $request->invoice['cfdi_use'];
            // $invoice->type          = $request->invoice['type'];

            // $invoice->save();


            return ApiResponseController::response('Orden actualizada exitosamente', 200, $order);
        } else {
            return ApiResponseController::response('La orden no existe', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $order = Order::with('orderCourses', 'student')->where('id', $id)->first();
        if (!$order) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        $user = request()->user();
        if ($user->role_id != 1 && $user->role_id != 2) {
            return ApiResponseController::response('No tienes permisos para realizar esta acción', 403);
        }

        $student = $order->student;
        $order_id = $order->id;
        foreach ($order->orderCourses as $orderCourse) {
            $orderCourse->certificationTests()->delete();
            $orderCourse->freezings()->delete();
            $orderCourse->extensions()->delete();


            $orderCourse->dateHistory()->delete();
        }

        $order->sapInstalations()->each(function ($item) {
            $item->sapTries()->delete();
            $item->delete();
        });
        $order->orderCourses()->delete();
        $order->dues()->delete();
        // $order->invoice()->delete();

        $order->delete();


        UserActivity::create([
            'user_id'     => $user->id,
            'description' => 'Eliminó la orden del estudiante ' . $student->name . ' con el id ' . $order_id . ' el ' . Carbon::now()->format('d/m/Y H:i:s')
        ]);

        return ApiResponseController::response('Registro eliminado con exito', 200);
    }


    public function updateTrakingInfo(Request $request, $id)
    {
        if (!$orderCourse = OrderCourse::find($id)) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }
        $orderCourse->start                = $request->start;
        $orderCourse->end                  = $request->end;
        $orderCourse->classroom_status     = $request->classroom_status;
        $orderCourse->license              = $request->license;
        $orderCourse->certification_status = $request->certification_status;
        $orderCourse->save();

        return ApiResponseController::response('Datos actualizados exitosamente', 200, $orderCourse);
    }

    private function syncRelation($relation, $data)
    {
        $model = $relation->getModel();
        $fillableColumns = $model->getFillable();

        $new = array_filter($data, fn ($item) => !isset($item['id']) || $item['id'] == null);
        $new = array_map(function ($item) use ($fillableColumns) {
            return array_intersect_key($item, array_flip($fillableColumns));
        }, $new);

        $fillableColumns[] = 'id';
        $existing = array_filter($data, fn ($item) => isset($item['id']) && $item['id'] != null);
        $existing = array_map(function ($item) use ($fillableColumns) {
            return array_intersect_key($item, array_flip($fillableColumns));
        }, $existing);

        foreach ($existing as $item) {
            $existingModel = $model->newQuery()->find($item['id']);
            if ($existingModel) {
                $existingModel->fill($item);
                $existingModel->save();
            }
        }

        // Crear nuevos registros
        $record = $relation->createMany($new);

        // Obtener el índice máximo de $new
        $maxIndex = !empty($new) ? max(array_keys($new)) : null;

        if ($maxIndex !== null) {
            // Obtener el modelo actual
            $modelName = explode('\\', get_class($model));
            $modelName = $modelName[count($modelName) - 1];

            if ($modelName == "Extension" || $modelName == "Freezing") {
                $orderCourse = OrderCourse::find($new[$maxIndex]['order_course_id']);
                $data = [
                    'order_course_id' => $new[$maxIndex]['order_course_id'],
                    'order_id' => $new[$maxIndex]['order_id'],
                    'start_date' => $orderCourse->start,
                    'end_date' => $modelName == 'Freezing' ? $new[$maxIndex]['finish_date'] : $orderCourse->end,
                    'type' => $modelName == 'Freezing' ? 'Congelación' : 'Extension'
                ];
                $data[strtolower($modelName) . '_id'] = $record[0]['id'];
                DatesHistory::create($data);

                return [$new, $modelName];
            }
        }

        return [$new, null];
    }





    function getOptions()
    {

        $courses        = Course::with('prices.currency')->get();
        $prices         = Price::with('currency')->get();
        $currencies     = Currency::all();
        $paymentMethods = PaymentMethod::all();
        $documentTypes  = DocumentType::all();
        $messages       = Message::all();
        // $user           = User::whereHas('role', function ($query) {
        //     $query->where('name', 'Tecnico de instalación');
        // })->get()->append(['unavailableTimes', 'bussyTimes']);

        $options = [
            'courses'        => $courses,
            'prices'         => $prices,
            'currencies'     => $currencies,
            'paymentMethods' => $paymentMethods,
            'documentTypes'  => $documentTypes,
            // 'staff'          => $user,
            'messages'      => $messages
        ];

        return ApiResponseController::response('Consulta exitosa', 200, $options);
    }

    public function datesHistory($id)
    {
        $datesHistory = DatesHistory::with('course')->where('order_course_id', $id)->orderBy('created_at', 'ASC')->get();
        return ApiResponseController::response('Consulta exitosa', 200, $datesHistory);
    }
}
