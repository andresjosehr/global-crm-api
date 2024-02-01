<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\SapInstalation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SapInstalationsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $sapInstalation = new SapInstalation();

        $sap = $request->all();
        $sap['start_datetime'] = Carbon::parse($sap['date'])->format('Y-m-d') . ' 00:00:00';
        $sapInstalation->fill($sap);
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation saved', 200, $sapInstalation);
    }


    public function update(Request $request, $id)
    {
        $sapInstalation = SapInstalation::find($id);

        // return $request->time;
        $sap = $request->all();
        $sap['start_datetime'] = Carbon::parse($sap['date'])->format('Y-m-d');
        $time = $request->time ? $request->time : '00:00:00';
        $sap['start_datetime'] = $sap['start_datetime'] . ' ' . $time;
        $sap['end_datetime'] = Carbon::parse($sap['start_datetime'])->addMinutes(30)->format('Y-m-d H:i:s');
        $sapInstalation->fill($sap);
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation updated', 200, $sapInstalation);
    }
}
