<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Order;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentsController extends Controller
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

        $searchString = $request->input('searchString') ? $request->input('searchString') : '';
        $searchString = $request->input('searchString') != 'null' ? $request->input('searchString') : '';

        $users = Student::when($searchString, function ($q) use ($searchString) {
            $q->where('name', 'LIKE', "%$searchString%")
                ->orWhere('country', 'LIKE', "%$searchString%")
                ->orWhere('phone', 'LIKE', "%$searchString%")
                ->orWhere('document', 'LIKE', "%$searchString%");
        })
            ->orderByDesc('id')
            ->paginate($perPage);

        return ApiResponseController::response('Consulta Exitosa', 200, $users);
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
        $student                       = new Student();
        $student->name                = $request->input('name');
        $student->country_id           = $request->input('country_id');
        $student->document_type_id           = $request->input('document_type_id');

        $student->phone               = $request->input('phone');
        $student->document            = $request->input('document');
        $student->email               = $request->input('email');
        $student->save();

        return ApiResponseController::response('Usuario creado con exito', 200, $student);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$student = Student::with('orders.orderCourses.course', 'orders.orderCourses.extensions', 'orders.orderCourses.certificationTests', 'orders.orderCourses.sapInstalations', 'orders.orderCourses.freezings', 'orders.orderCourses.dateHistory', 'orders.currency', 'orders.dues', 'orders.user', 'orders.invoice')->find($id)) {
            return ApiResponseController::response('', 204);
        }

        return ApiResponseController::response('Consulta exitosa', 200, $student);
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
        $student                       = Student::find($id);

        if (!$student) {
            return ApiResponseController::response('Estudiante no encontrado', 404);
        }

        $student->name                = $request->input('name');
        $student->country_id             = $request->input('country_id');
        $student->phone               = $request->input('phone');

        $student->email               = $request->input('email');



        if ($request->input('document_type_id') == 'otro') {
            // Check if exists
            if (!DocumentType::where('name', $request->input('document_type_name'))->exists()) {
                $documentType = new DocumentType();
                $documentType->name = $request->input('document_type_name');
                $documentType->custom = true;
                $documentType->country_id = $request->input('country_id');
                $documentType->save();
                $student->document_type_id = $documentType->id;
            }
        } else {
            $student->document_type_id     = $request->input('document_type_id');
        }


        $student->save();

        return ApiResponseController::response('Usuario actualizado con exito', 200, $student);
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


    function checkTermsAccess(Request $request, $key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', 0)->first();

        if (!$order) {
            return ApiResponseController::response('Unauthorized', 401);
        }

        return ApiResponseController::response('Authorized', 200);
    }


    function getTermsInfo($key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', false)->with('student', 'orderCourses.course', 'dues', 'currency', 'student')->first();

        if (!$order) {
            return ApiResponseController::response('No se encontró la orden', 404);
        }

        return ApiResponseController::response('Consulta exitosa', 200, $order);
    }


    function confirmTermsInfo(Request $request, $key)
    {
        $order = Order::where('key', $key)->where('terms_confirmed_by_student', false)->first();

        if (!$order) {
            return ApiResponseController::response('No se encontró la orden', 404);
        }


        $student                       = Student::find($order->student->id);

        $student->name                = $request->input('name');
        $student->country_id             = $request->input('country_id');
        $student->phone               = $request->input('phone');

        $student->email               = $request->input('email');



        if ($request->input('document_type_id') == 'otro') {
            // Check if exists
            if (!DocumentType::where('name', $request->input('document_type_name'))->exists()) {
                $documentType = new DocumentType();
                $documentType->name = $request->input('document_type_name');
                $documentType->custom = true;
                $documentType->country_id = $request->input('country_id');
                $documentType->save();
                $student->document_type_id = $documentType->id;
            }
        } else {
            $student->document_type_id     = $request->input('document_type_id');
        }

        $order->terms_confirmed_by_student = true;
        $order->save();

        return ApiResponseController::response('Consulta exitosa', 200, $order);
    }
}
