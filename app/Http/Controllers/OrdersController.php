<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Currency;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Price;
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

        // Courses
        $order->courses()->createMany($request->courses);
        $order->courses()->createMany($request->free_courses);

        // Dues
        $order->dues()->createMany($request->dues);


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
            $order = Order::with('courses', 'dues', 'student', 'currency', 'price')->find($id);
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
            $order->courses()->delete();
            $order->courses()->createMany($request->courses);
            $order->courses()->createMany($request->free_courses);

            // Dues
            $order->dues()->delete();
            $order->dues()->createMany($request->dues);

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

    function getOptions()
    {

        $courses = Course::with('prices.currency')->get();
        $prices = Price::with('currency')->get();
        $currencies = Currency::all();
        $paymentMethods = PaymentMethod::all();
        $documentTypes = DocumentType::all();

        $options = [
            'courses' => $courses,
            'prices' => $prices,
            'currencies' => $currencies,
            'paymentMethods' => $paymentMethods,
            'documentTypes' => $documentTypes,
        ];

        return ApiResponseController::response('Consulta exitosa', 200, $options);
    }
}
