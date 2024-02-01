<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\Freezing;
use Illuminate\Http\Request;

class FreezingsController extends Controller
{
    public function saveDraft(Request $request)
    {
        $freezing = new Freezing();
        $freezing->order_id = $request->order_id;
        $freezing->order_course_id = $request->order_course_id;

        $freezing->save();

        return ApiResponseController::response('Freezing saved', 200, $freezing);
    }


    public function update(Request $request)
    {
        $datesFields = ['start_date', 'finish_date', 'return_date', 'payment_date', 'new_return_date', 'new_finish_date'];
        // return $request->all();
        foreach($request->all() as $free) {


            $fillable = (new Freezing())->getFillable();
            $free2 = array_filter($free, function($key) use ($fillable) {
                return in_array($key, $fillable);
            }, ARRAY_FILTER_USE_KEY);

            foreach($datesFields as $date) {
                if(isset($free2[$date])) {
                    $free2[$date] = date('Y-m-d', strtotime($free2[$date]));
                }
            }


            Freezing::where('id', $free['id'])->update($free2);
        }

        return ApiResponseController::response('Exito', 200);
    }
}
