<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\DatesHistory;
use App\Models\Due;
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


    public function update(Request $request, $id)
    {

        $cert     = $request->all();
        $fillable = (new Extension())->getFillable();
        $cert     = array_filter($cert, function ($key) use ($fillable) {
            return in_array($key, $fillable);
        }, ARRAY_FILTER_USE_KEY);


        if (gettype($cert['months']) == 'integer') {

            if (!DatesHistory::where('extension_id', $cert['id'])->first()) {

                $orderCourse = OrderCourse::where('id', $cert['order_course_id'])->first();

                $holidays = DatesHistory::all();
                $end = Carbon::parse($orderCourse->end)->addMonths($cert['months'])->format('Y-m-d');
                if ($holidays->contains('date', $orderCourse->end) || Carbon::parse($orderCourse->end)->isSunday()) {
                    $end = Carbon::parse($orderCourse->end)->addMonths($cert['months'])->addDay();
                }


                DatesHistory::create([
                    'order_id'        => $cert['order_id'],
                    'order_course_id' => $cert['order_course_id'],
                    'start_date'      => $orderCourse->start,
                    'end_date'        => $end->format('Y-m-d'),
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

        Extension::where('id', $cert['id'])->update($cert);

        $extension = Extension::where('id', $cert['id'])->first();

        self::updatePayment($request, $extension->due_id);

        return ApiResponseController::response('Exito', 200);
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
