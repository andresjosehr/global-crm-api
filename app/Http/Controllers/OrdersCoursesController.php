<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderCourse;
use Illuminate\Http\Request;

class OrdersCoursesController extends Controller
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
        $orderCourse = OrderCourse::with('course', 'extensions.due', 'certificationTests', 'freezings.due', 'dateHistory')->find($id);
        if (!$orderCourse) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        return ApiResponseController::response('Consulta exitosa', 200, $orderCourse);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if ($user->role_id != 1) {
            return ApiResponseController::response('No tienes permisos para realizar esta accion', 403);
        }


        if (!$orderCourse = OrderCourse::find($id)) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        $orderCourse->certificationTests()->delete();
        $orderCourse->freezings()->delete();
        $orderCourse->extensions()->delete();
        foreach ($orderCourse->sapInstalations() as $sapInstalation) {
            $sapInstalation->sapTries()->delete();
        }
        $orderCourse->dateHistory()->delete();

        $orderCourse->delete();

        return ApiResponseController::response('Registro eliminado con exito', 200);
    }
}
