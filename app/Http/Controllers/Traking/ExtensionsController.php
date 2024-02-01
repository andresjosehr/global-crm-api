<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\Extension;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExtensionsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $sapInstalation = new Extension();
        $sapInstalation->fill($request->all());
        $sapInstalation->save();

        return ApiResponseController::response('Sap instalation saved', 200, $sapInstalation);
    }


    public function update(Request $request)
    {
        foreach($request->all() as $cert) {

            $fillable = (new Extension())->getFillable();
            $cert = array_filter($cert, function($key) use ($fillable) {
                return in_array($key, $fillable);
            }, ARRAY_FILTER_USE_KEY);

            $cert['payment_date'] = $cert['payment_date'] ? Carbon::parse($cert['payment_date'])->format('Y-m-d') : null;

            Extension::where('id', $cert['id'])->update($cert);
        }

        return ApiResponseController::response('Exito', 200);
    }
}
