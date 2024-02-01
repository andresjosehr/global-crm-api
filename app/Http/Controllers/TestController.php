<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Mails\CoreMailsController;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Processes\StudentsExcelController;
use App\Models\Order;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($order_id)
    {
        $google_sheet = new GoogleSheetController();

        $spreadsheetId = '1if36irD9uuJDWcPpYY6qElfdeTiIlEVsUZNmrwDdxWs';
        $range = 'ENERO 24!A1:AH';

        // 1. Obtener los datos existentes para encontrar la primera fila vacía
        $response = $google_sheet->service->spreadsheets_values->get($spreadsheetId, $range);
        $rows = $response->getValues();
        $header = array_shift($rows);
        $rows = array_map(function ($row) {
            return count($row);
        }, $rows);

        $emptyRow = array_search(1, $rows) + 2; // Suponiendo que la primera columna debe estar vacía

        $order = Order::where('id', $order_id)->with('orderCourses.course', 'dues.paymentMethod', 'currency', 'student.user')->first();

        $courses = array_reduce($order->orderCourses->toArray(), function ($carry, $item) {
            $carry  .= $item['course']['short_name'];
            return $carry . ' + ';
        }, '');
        $courses = substr($courses, 0, -3);
        $order->student['courses'] = $courses;
        $start = $order->orderCourses->min('start');
        $order->student['start'] = Carbon::parse($start)->format('d/m/Y');
        $order->student['license'] = $order->orderCourses->first()->license . ' de licensia y aula virtual';
        $order->student['user'] = $order->student->user->name;
        $order->student['row'] = $emptyRow;

        $ref = [
            'row'       => 'A',
            'name'     => 'B',
            'document' => 'C',
            'courses'  => 'D',
            'phone'    => 'E',
            'email'    => 'F',
            'start'    => 'AB',
            'license'  => 'AC',
            'user'     => 'AD'
        ];

        foreach ($ref as $key => $col) {
            $dataToUpdate[] = ['column' => $col, 'value' => $order->student[$key] . ''];
        }

        $col = 'G';
        foreach ($order->dues as $due) {
            $dataToUpdate[] = ['column' => $col, 'value' => $due->amount . ' ' . $order->currency->iso_code];
            $col++;
            $date = Carbon::parse($due->date)->format('d/m/Y');
            $dataToUpdate[] = ['column' => $col, 'value' => $date];
            $col++;
            $dataToUpdate[] = ['column' => $col, 'value' => $due->paymentMethod];
            $col++;
        }

        // 'sheet_id'          => '1if36irD9uuJDWcPpYY6qElfdeTiIlEVsUZNmrwDdxWs',
        // 'course_row_number' => $emptyRow,
        // 'tab_id'            => '1992733426',
        // Add all this properties to the array

        $dataToUpdate = array_map(function ($item) use ($emptyRow, $spreadsheetId) {
            $item['sheet_id'] = $spreadsheetId;
            $item['course_row_number'] = $emptyRow;
            $item['tab_id'] = '1438941447';
            return $item;
        }, $dataToUpdate);
        // return $dataToUpdate;

        // return $dataToUpdate;

        $google_sheet = new GoogleSheetController();
        $data = $google_sheet->transformData($dataToUpdate);
        $data = $google_sheet->prepareRequests($data);

        $google_sheet->updateGoogleSheet($data);

        return "Exito";
    }

    // public  sendDebugNotification($user_id)
}
