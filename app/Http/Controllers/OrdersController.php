<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CertificationTest;
use App\Models\Course;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderCourse;
use App\Models\PaymentMethod;
use App\Models\Price;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
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

        // Get user
        $user = $request->user();
        $id = $user->id;

        $order = new \App\Models\Order();


        $order->student_id = $request->student_id;
        $order->currency_id = $request->currency_id;
        $order->enrollment_sheet = $request->enrollment_sheet;
        $order->payment_mode = $request->payment_mode;
        $order->price_id = $request->price_id;
        $order->price_amount = $request->price_amount;
        $order->created_by = $id;

        // Generate random key
        $order->key = md5(microtime());


        $order->save();

        // orderCourses
        $order->orderCourses()->createMany($request->order_courses);

        // Dues
        $order->dues()->createMany($request->dues);


        $freeCourses = [6, 7, 8, 9];

        foreach ($order->orderCourses as $course) {

            $course_id = $course->course_id;
            $limit = array_search($course_id, $freeCourses) ? 3 : 5;

            if ($course_id != 6) {
                for ($i = 0; $i < $limit; $i++) {
                    if ($i < $limit - 1) {
                        $name = "Examen de certificación " . ($i + 1);
                    } else {
                        $name = "Ponderación";
                    }

                    $certificationTest = new CertificationTest();
                    $certificationTest->description = $name;
                    $certificationTest->order_id = $order->id;
                    $certificationTest->order_course_id = $course->id;
                    $certificationTest->enabled = $i < 3;
                    $certificationTest->status = 'Sin realizar';
                    $certificationTest->premium = array_search($course_id, $freeCourses) ? false : $i >= 3;
                    $certificationTest->save();
                }
            }

            if ($course_id == 6) {
                $cert = ['BASICO', 'INTERMEDIO', 'AVANZADO'];
                foreach ($cert as $c) {
                    for ($i = 0; $i < 3; $i++) {
                        $name = $i < 3 ? "Examen de certificación " . $c . " " . ($i + 1) : "Ponderación";
                        $certificationTest = new CertificationTest();
                        $certificationTest->description = $c;
                        $certificationTest->order_id = $order->id;
                        $certificationTest->order_course_id = $course->id;
                        $certificationTest->enabled = true;
                        $certificationTest->status = 'Sin realizar';
                        $certificationTest->premium = false;
                        $certificationTest->save();
                    }
                }
            }
        }


        $invoice = new Invoice();
        if ($request->invoice['tax_situation_proof_changed']) {
            // Convert base64 to pdf
            $file = $request->invoice['tax_situation_proof'];
            $file = str_replace('data:application/pdf;base64,', '', $file);
            $file = str_replace(' ', '+', $file);
            $fileName = 'tax_situation_proof_' . $order->id . '.pdf';
            \File::put(storage_path() . '/app/public/invoices/' . $fileName, base64_decode($file));
            $invoice->tax_situation_proof = $fileName;
        }

        $invoice->requested = $request->invoice['requested'];
        $invoice->ruc = $request->invoice['ruc'];
        $invoice->business_name = $request->invoice['business_name'];
        $invoice->email = $request->invoice['email'];
        $invoice->tax_situation = $request->invoice['tax_situation'];
        $invoice->tax_regime = $request->invoice['tax_regime'];
        $invoice->address = $request->invoice['address'];
        $invoice->postal_code = $request->invoice['postal_code'];
        $invoice->cellphone = $request->invoice['cellphone'];
        $invoice->cfdi_use = $request->invoice['cfdi_use'];
        $invoice->type = $request->invoice['type'];
        $invoice->order_id = $order->id;

        $invoice->save();


        // Get id
        $order = Order::with('orderCourses.course', 'orderCourses.certificationTests', 'orderCourses.freezings', 'orderCourses.sapInstalations', 'dues', 'student', 'currency', 'price')->find($order->id);

        return ApiResponseController::response('Orden creada exitosamente', 201, $order);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (Order::where('id', $id)->exists()) {
            $order = Order::with('orderCourses.course', 'dues', 'student', 'currency', 'price', 'certificationTests')->find($id);
            return ApiResponseController::response('Consulta exitosa', 200, $order);
        } else {
            return ApiResponseController::response('La orden no existe', 404);
        }
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

            $order->student_id = $request->student_id;
            $order->currency_id = $request->currency_id;
            $order->enrollment_sheet = $request->enrollment_sheet;
            $order->payment_mode = $request->payment_mode;
            $order->price_id = $request->price_id;
            $order->price_amount = $request->price_amount;
            $order->terms_confirmed_by_student = $request->terms_confirmed_by_student;

            $order->save();

            // Courses
            $order->orderCourses()->delete();
            $order->orderCourses()->createMany($request->courses);
            $order->orderCourses()->createMany($request->free_courses);

            // Dues
            $order->dues()->delete();
            $order->dues()->createMany($request->dues);

            // Certification Test Sync
            $order->certificationTests()->delete();
            $order->certificationTests()->createMany($request->certification_tests);


            $invoice = Invoice::where('order_id', $order->id)->first();

            if ($request->invoice['tax_situation_proof_changed']) {
                $file = $request->invoice['tax_situation_proof'];
                $file = str_replace('data:application/pdf;base64,', '', $file);
                $file = str_replace(' ', '+', $file);
                $date = date('Y-m-d-H-i-s');
                $fileName = explode('.', $request->invoice['tax_situation_proof_name'])[0] . '_' . $date . '.pdf';
                \File::put(storage_path() . '/app/public/invoices/' . $fileName, base64_decode($file));
                $invoice->tax_situation_proof = $fileName;
            }

            $invoice->requested = $request->invoice['requested'];
            $invoice->ruc = $request->invoice['ruc'];
            $invoice->business_name = $request->invoice['business_name'];
            $invoice->email = $request->invoice['email'];
            $invoice->tax_situation = $request->invoice['tax_situation'];
            $invoice->tax_regime = $request->invoice['tax_regime'];
            $invoice->address = $request->invoice['address'];
            $invoice->postal_code = $request->invoice['postal_code'];
            $invoice->cellphone = $request->invoice['cellphone'];
            $invoice->cfdi_use = $request->invoice['cfdi_use'];
            $invoice->type = $request->invoice['type'];

            $invoice->save();


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
        if (!$order = Order::find($id)) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        // Delete courses and dues
        $order->courses()->delete();
        $order->dues()->delete();

        $order->delete();

        return ApiResponseController::response('Registro eliminado con exito', 200);
    }


    public function updateTrakingInfo(Request $request, $id)
    {
        if (!$order = Order::find($id)) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }
        foreach ($request->courses as $orderCourse) {

            $orderCourseDB = OrderCourse::find($orderCourse['id']);

            // Sincronizar certificationTests
            $this->syncRelation($orderCourseDB->certificationTests(), $orderCourse['certification_tests']);

            // Sincronizar freezings
            $this->syncRelation($orderCourseDB->freezings(), $orderCourse['freezings']);

            // Sincronizar extensions
            $this->syncRelation($orderCourseDB->extensions(), $orderCourse['extensions']);

            $i = 0;
            foreach($orderCourse['sap_instalations'] as $sapInstalation) {
                $dateTime = explode('T', $sapInstalation['date'])[0];
                $dateTime = $dateTime . ' ' . $sapInstalation['time'];
                $orderCourse['sap_instalations'][$i]['start_datetime'] = Carbon::parse($dateTime)->format('Y-m-d H:i:s');
                $orderCourse['sap_instalations'][$i]['end_datetime'] = Carbon::parse($dateTime)->addMinutes(30)->format('Y-m-d H:i:s');
                $i++;
            }

            // Sincronizar sapInstalations
            $this->syncRelation($orderCourseDB->sapInstalations(), $orderCourse['sap_instalations']);
        }


        $order = Order::with('orderCourses.course', 'orderCourses.certificationTests', 'orderCourses.sapInstalations', 'orderCourses.freezings', 'orderCourses.extensions', 'orderCourses.dateHistory')->find($id);
        return ApiResponseController::response('Orden actualizada exitosamente', 200, $order);
    }

    private function syncRelation($relation, $data)
    {
        $model = $relation->getModel();
        $fillableColumns = $model->getFillable();

        // Filtrar los registros existentes y los nuevos
        $existing = array_filter($data, fn ($item) => isset($item['id']) && $item['id'] != null);
        $new = array_filter($data, fn ($item) => !isset($item['id']) || $item['id'] == null);

        $new = array_map(function ($item) use ($fillableColumns) {
            return array_intersect_key($item, array_flip($fillableColumns));
        }, $new);

        $fillableColumns[] = 'id';
        $existing = array_map(function ($item) use ($fillableColumns) {
            return array_intersect_key($item, array_flip($fillableColumns));
        }, $existing);



        // Actualizar registros existentes
        foreach ($existing as $item) {
            $relation->where('id', $item['id'])->update($item);
        }

        // Obtener los nombres de las columnas "fillable"


        // Filtrar las propiedades de los nuevos registros


        // Crear nuevos registros
        $relation->createMany($new);

        return $new;
    }


    function getOptions()
    {

        $courses = Course::with('prices.currency')->get();
        $prices = Price::with('currency')->get();
        $currencies = Currency::all();
        $paymentMethods = PaymentMethod::all();
        $documentTypes = DocumentType::all();
        $user = User::whereHas('role', function ($query) {
            $query->where('name', 'Tecnico de instalación');
        })->get()->append(['unavailableTimes', 'bussyTimes']);

        $options = [
            'courses' => $courses,
            'prices' => $prices,
            'currencies' => $currencies,
            'paymentMethods' => $paymentMethods,
            'documentTypes' => $documentTypes,
            'staff' => $user
        ];

        return ApiResponseController::response('Consulta exitosa', 200, $options);
    }
}
