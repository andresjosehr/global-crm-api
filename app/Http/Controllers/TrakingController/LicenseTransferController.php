<?php

namespace App\Http\Controllers\TrakingController;

use App\Http\Controllers\Controller;
use App\Models\DatesHistory;
use App\Models\Holiday;
use App\Models\OrderCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LicenseTransferController extends Controller
{
    public function saveLicenceTransfer(Request $request)
    {
        $holiday = Holiday::all();

        $orderCourse = OrderCourse::find($request->order_course_id);
        $end = Carbon::parse($orderCourse->end)->addMonths($request->license_months_transfer);

        while ($holiday->contains('date', $end->format('Y-m-d')) || $end->dayOfWeek == 0) {
            $end->addDay();
        }

        $orderCourse->end = $end->format('Y-m-d');
        $orderCourse->license = explode(' ', $orderCourse->license)[0] + $request->license_months_transfer;
        $orderCourse->license = $orderCourse->license . ' meses';
        $orderCourse->save();

        DatesHistory::create([
            'order_course_id' => $orderCourse->id,
            'start_date'      => $orderCourse->start,
            'end_date'        => $end->format('Y-m-d'),
            'type'            => 'Transferencia de licencia (Sumada)'
        ]);

        $otherOrderCourse = OrderCourse::find($request->order_course_subtract_id);
        $otherOrderCourse->license = explode(' ', $otherOrderCourse->license)[0] - $request->license_months_transfer;
        $otherOrderCourse->license = $otherOrderCourse->license < 2 ? '1 mes' : $otherOrderCourse->license . ' meses';
        $otherOrderCourse->save();

        DatesHistory::create([
            'order_course_id' => $otherOrderCourse->id,
            'start_date'      => $otherOrderCourse->start,
            'end_date'        => $otherOrderCourse->end,
            'type'            => 'Transferencia de licencia (Restada)'
        ]);

        $orderCourses = OrderCourse::where('order_id', $orderCourse->order_id)
        ->where('start', '>', $orderCourse->start)
        ->where('type', $orderCourse->type)
        ->orderBy('start', 'asc')
        ->get();

        $finish_date = $orderCourse->end;
        foreach($orderCourses as $order_course) {
            $newStartDate = Carbon::parse($finish_date)->addDays(1);

            $holidays = Holiday::all();
            // if new start date is a holiday or sunday, add one day
            while ($holidays->contains('date', $newStartDate->format('Y-m-d')) || $newStartDate->dayOfWeek == 0) {
                $newStartDate->addDays(1);
            }

            $license = explode(' ', $order_course->license)[0];
            $newEndDate = Carbon::parse($newStartDate)->addMonths($license);

            while ($holidays->contains('date', $newEndDate->format('Y-m-d')) || $newEndDate->dayOfWeek == 0) {
                $newEndDate->addDays(1);
            }

            $finish_date = $newEndDate;
            OrderCourse::where('id', $order_course->id)->update(['start' => $newStartDate, 'end' => $newEndDate]);

            DatesHistory::create([
                'order_course_id' => $order_course->id,
                'start_date'      => $newStartDate,
                'end_date'        => $newEndDate,
                'type'            => 'Transferencia de licencia (Actualización de fechas)'
            ]);
        }

        return response()->json(['message' => 'Licencia transferida con éxito']);

    }
}
