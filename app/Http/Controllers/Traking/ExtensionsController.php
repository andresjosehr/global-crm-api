<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\DatesHistory;
use App\Models\Extension;
use App\Models\OrderCourse;
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


            if(gettype($cert['months']) == 'integer') {

                if(!DatesHistory::where('extension_id', $cert['id'])->first()){
                    $orderCourse = OrderCourse::where('id', $cert['order_course_id'])->first();
                    DatesHistory::create([
                        'order_id'        => $cert['order_id'],
                        'order_course_id' => $cert['order_course_id'],
                        'start_date'      => $orderCourse->start,
                        'end_date'        => Carbon::parse($orderCourse->end)->addMonths($cert['months'])->format('Y-m-d'),
                        'extension_id'    => $cert['id'],
                        'type'            => 'Extension',
                    ]);

                    // Get date_history created
                    $dateHistory = DatesHistory::where('extension_id', $cert['id'])->first();

                    OrderCourse::where('id', $cert['order_course_id'])->update([
                        'end' => $dateHistory->end_date
                    ]);
                }
            }

            $cert['payment_date'] = $cert['payment_date'] ? Carbon::parse($cert['payment_date'])->format('Y-m-d') : null;

            Extension::where('id', $cert['id'])->update($cert);
        }

        return ApiResponseController::response('Exito', 200);
    }
}
