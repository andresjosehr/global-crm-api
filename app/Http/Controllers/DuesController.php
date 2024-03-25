<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traking\ExtensionsController;
use App\Http\Controllers\Traking\FreezingsController;
use App\Models\Due;
use App\Models\Extension;
use App\Models\Freezing;
use Carbon\Carbon;
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


        $priorityPayments = Due::with('student', 'currency', 'paymentMethod')
            ->whereIn('payment_reason', ['Desbloqueo SAP', 'Extension', 'Examen de certificación'])->where('payment_verified_at', null)->orderBy('date', 'asc')
            ->where('payment_verified_at', null)
            ->whereNotNull('payment_receipt')
            ->get();


        return ApiResponseController::response('Dues', 200, [
            'dues' => $dues,
            'priorityDues' => $priorityPayments
        ]);
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


            // if payment date is greater than 12/03/2024
            $dueDate = Carbon::parse($due->date);
            $limitDate = Carbon::parse('2024-03-12');

            if ($dueDate->gt($limitDate)) {


                if ($due->payment_reason == 'Extension') {
                    $extension = Extension::where('due_id', $due->id)->first();
                    ExtensionsController::sendNotificacion($extension->id);
                }

                if ($due->payment_reason == 'Congelación') {
                    $freezing = Freezing::where('due_id', $due->id)->whereSet(false)->first();

                    if ($freezing) {
                        if ($freezing->courses == 'all') {
                            FreezingsController::setFreezingMany($freezing);
                        }
                        if ($freezing->courses == 'single') {
                            FreezingsController::setFreezingSingle($freezing);
                        }
                    }
                }
            }
        }

        if ($value === 'n') {
            $due->payment_verified_at = null;
            $due->payment_verified_by = null;
        }


        $due->save();

        return ApiResponseController::response('Pago verificado con exito', 200);
    }

    public function updatePayment(Request $request, $due_id)
    {

        $due = Due::find($due_id);
        // Get fillable fields
        $fillable = (new Due())->getFillable();
        $data = array_filter($request->all(), function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);

        $due->fill($data);

        $due->save();

        return true;
    }
}
