<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Currency;
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
        if(Order::where('id', $id)->exists()) {
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
        if(Order::where('id', $id)->exists()) {
            $order = Order::find($id);

            $order->student_id = $request->student_id;
            $order->currency_id = $request->currency_id;
            $order->enrollment_sheet = $request->enrollment_sheet;
            $order->payment_mode = $request->payment_mode;
            $order->price_id = $request->price_id;
            $order->price_amount = $request->price_amount;

            $order->save();

            // Courses
            $order->courses()->delete();
            $order->courses()->createMany($request->courses);
            $order->courses()->createMany($request->free_courses);

            // Dues
            $order->dues()->delete();
            $order->dues()->createMany($request->dues);

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
        if(!$order = Order::find($id)){
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        // Delete courses and dues
        $order->courses()->delete();
        $order->dues()->delete();

        $order->delete();

        return ApiResponseController::response('Registro eliminado con exito', 200);
    }

    function getOptions() {

        $courses = Course::with('prices.currency')->get();
        $prices = Price::with('currency')->get();
        $currencies = Currency::all();
        $paymentMethods = PaymentMethod::all();

        $options = [
            'courses' => $courses,
            'prices' => $prices,
            'currencies' => $currencies,
            'paymentMethods' => $paymentMethods,
        ];

        return ApiResponseController::response('Consulta exitosa', 200, $options);
    }


}
