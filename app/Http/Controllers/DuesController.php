<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traking\ExtensionsController;
use App\Models\Due;
use App\Models\Extension;
use Illuminate\Http\Request;

class DuesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Paginate
        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;

        $dues = Due::with('student', 'currency', 'paymentMethod')
            ->when($request->date, function ($query) use ($request) {
                $query->whereDate('date', $request->date);
            })
            ->when($request->payment_verified, function ($query) use ($request) {
                $query->where('payment_verified_at', $request->payment_verified == 'Sin verificar' ? null : '!=', null)
                    ->when($request->payment_verified == 'Sin verificar', function ($query) {
                        return $query->whereNotNull('payment_receipt');
                    });
            })
            ->when($request->payment_reason, function ($query) use ($request) {
                $query->where('payment_reason', $request->payment_reason);
            })
            ->when($request->student, function ($query) use ($request) {
                $query->whereHas('student', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->student . '%')
                        ->orWhere('email', 'like', '%' . $request->student . '%')
                        ->orWhere('phone', 'like', '%' . $request->student . '%');
                });
            })
            ->when($request->currency_id && $request->currency_id != 'all', function ($query) use ($request) {
                $query->where('currency_id', $request->currency_id);
            })
            ->when($request->payment_method_id && $request->payment_method_id != 'all', function ($query) use ($request) {
                $query->where('payment_method_id', $request->payment_method_id);
            })
            ->orderBy('date', 'desc')

            ->paginate($perPage);
        return ApiResponseController::response('Dues', 200, $dues);
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
        //
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
    public function destroy($id)
    {
        if (!$due = Due::find($id)) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        $due->delete();

        return ApiResponseController::response('Registro eliminado con exito', 200);
    }



    public function verifiedPayment(Request $request, $id, $value)
    {
        $user = $request->user();
        $due = Due::find($id);

        if (!$due) {
            return ApiResponseController::response('No se encontro el registro', 204);
        }

        if ($value === 'y') {
            $due->payment_verified_at = now();
            $due->payment_verified_by = $user->id;
            if ($due->payment_reason == 'Extension') {
                $extension = Extension::where('due_id', $due->id)->first();
                ExtensionsController::sendNotificacion($extension->id);
            }
        }


        $due->save();

        return ApiResponseController::response('Pago verificado con exito', 200);
    }
}
