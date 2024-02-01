<?php

namespace App\Http\Controllers\Traking;

use App\Http\Controllers\ApiResponseController;
use App\Http\Controllers\Controller;
use App\Models\Freezing;
use App\Models\OrderCourse;
use Carbon\Carbon;
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

        if(count($request->all()) == 0) {
            return ApiResponseController::response('No hay datos', 422);
        }

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

            // Check if current date is between start_date and return_date
            if(isset($free2['start_date']) && isset($free2['return_date'])) {

                $currentDate = Carbon::now();
                $startDate = Carbon::parse($free2['start_date']);
                $returnDate = Carbon::parse($free2['return_date']);
                if($currentDate->between($startDate, $returnDate)) {
                    OrderCourse::where('id', $free['order_course_id'])->update(['classroom_status' => 'Congelado']);
                }
            }

            Freezing::where('id', $free['id'])->update($free2);
        }

        $order_course_id = $request->all()[0]['order_course_id'];
        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $order_course_id)->orderBy('id', 'desc')->first();
        $now = Carbon::now();
        $courseFreezing = false;
        if($now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            OrderCourse::where('id', $order_course_id)->update(['classroom_status' => 'Congelado']);
            $courseFreezing = true;
        }

        return ApiResponseController::response('Exito', 200, ['freezing' => $courseFreezing]);
    }

    public function unfreezeCourse($id)
    {
        OrderCourse::where('id', $id)->update(['classroom_status' => 'Cursando']);
        // Get last freezing
        $lastFreezing = Freezing::where('order_course_id', $id)->orderBy('id', 'desc')->first();

        if(!$lastFreezing) {
            return ApiResponseController::response('No hay congelamientos', 422);
        }

        $now = Carbon::now();
        if(!$now->between(Carbon::parse($lastFreezing->start_date), Carbon::parse($lastFreezing->finish_date))) {
            return ApiResponseController::response('El curso no esta congelado', 422);
        }

        // Update return_date
        $newReturnDate = Carbon::now();
        $newFinishDate = Carbon::parse($lastFreezing->finish_date);
        $diff = $newReturnDate->diffInDays(Carbon::parse($lastFreezing->return_date));
        $newFinishDate->subDays($diff);

        Freezing::where('id', $lastFreezing->id)->update(['new_return_date' => $newReturnDate, 'new_finish_date' => $newFinishDate]);

        $lastFreezing = Freezing::where('order_course_id', $id)->orderBy('id', 'desc')->first();


        return ApiResponseController::response('Exito', 200, $lastFreezing);

    }
}
