<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CertificationTest;
use App\Models\Currency;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Sheets;
use Illuminate\Support\Facades\DB;

class ImportContorller extends Controller
{

    private $service;

    private $badCourses = [
        "PB"                   => "POWERBI BASICO",
        "POWER BI"             => "POWERBI BASICO",
        "MSPJ"                 => 'MS PROJECT',
        "MS"                   => 'MS PROJECT',
        "MSP"                  => 'MS PROJECT',
        "POWERBI"              => 'POWERBI BASICO',
        "MSPROJ"               => 'MS PROJECT',
        "EXCL"                 => 'EXCEL',
        "POWRB"                => 'POWERBI BASICO',
        "MSPROJECT"            => 'MS PROJECT',
        "EXCELL"               => 'EXCEL',
        "MS PRJ"               => 'MS PROJECT',
        "POWER BI AVANZADO"    => 'POWERBI AVANZADO',
        "SAP MM (PROMO) EXCEL" => 'SAP MM',
        "EXCEL EMPRESARIAL"    => 'EXCEL',
        "PP"                   => 'SAP PP',
        "MS PROJEC"            => 'MS PROJECT',
        "SAP  MM"              => 'SAP MM',
        "POWER BI  AVANZADO"   => 'POWERBI AVANZADO',
        "SAP ABAP"             => 'SAP PP'
    ];

    public function index()
    {

        self::createGoogleServiceInstance();

        $data = self::getSheetsData();
        // return $data;
        $data = self::formatCourses($data);
        $data = self::formatPayment($data);



        $users = [];
        foreach ($data as $row) {
            $users[] = [
                'name'           => $row['NOMBRE COMPLETO CLIENTE'],
                'phone'          => $row['TELÉFONO'] ? $row['TELÉFONO'] : $row['TELEFONO'],
                'document'       => $row['DOCUMENTO'],
                'email'          => $row['CORREO'],
                'classroom_user' => $row['USUARIO AULA'],
                'payment_mode'   => strpos($row['ESTADO'], 'CONTADO') !== false ? 'Contado' : 'Cuotas',
                'courses'        => $row['courses'],
                'payments'       => $row['payments'],
            ];
        }


        // disable foreign key check for this connection before truncating tables
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('students')->truncate();
        DB::table('orders')->truncate();
        DB::table('order_courses')->truncate();
        DB::table('dues')->truncate();
        DB::table('certification_tests')->truncate();
        // enable foreign key check for this connection after truncating tables
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($users as $user) {
            $student                 = new \App\Models\Student();
            $student->name           = $user['name'];
            $student->phone          = $user['phone'];
            $student->document       = $user['document'];
            $student->email          = $user['email'];
            $student->classroom_user = $user['classroom_user'];
            $student->save();

            $order               = new \App\Models\Order();
            $order->student_id   = $student->id;
            $order->payment_mode = $user['payment_mode'];
            $order->price_amount = array_sum(array_column($user['payments'], 'amount'));
            $order->currency_id  = isset($user['payments'][0]['currency_id']) ? $user['payments'][0]['currency_id'] : null;
            $order->save();

            foreach ($user['courses'] as $course) {
                // Get diff in months
                $license = null;
                if ($course['start'] && $course['end']) {
                    $start = Carbon::createFromFormat('Y-m-d', $course['start']);
                    $end   = Carbon::createFromFormat('Y-m-d', $course['end']);
                    $diff  = $start->diffInMonths($end);
                    $license = $diff <= 3 ? '3 meses' : '6 meses';
                }

                $orderCourse            = new \App\Models\OrderCourse();
                $orderCourse->order_id  = $order->id;
                $orderCourse->course_id = $course['course_id'];
                $orderCourse->sap_user  = $course['sap_user'];
                $orderCourse->start     = $course['start'];
                $orderCourse->end       = $course['end'];
                $orderCourse->license   = $license;
                $orderCourse->type      = $course['type'];
                $orderCourse->save();
            }

            foreach ($user['payments'] as $payment) {
                $due              = new \App\Models\Due();
                $due->order_id    = $order->id;
                $due->currency_id = $payment['currency_id'];
                $due->amount      = $payment['amount'];
                $due->date        = $payment['date'];
                $due->save();
            }

            $freeCourses = [6, 7, 8, 9];

            $order = \App\Models\Order::with('orderCourses')->find($order->id);
            foreach ($order->orderCourses as $course) {
                $course_id = $course->course_id;

                $limit = ($course_id == 6) ? 12 : (in_array($course_id, $freeCourses) ? 4 : 6);

                for ($i = 0; $i < $limit; $i++) {
                    $certificationTest = new CertificationTest();
                    $certificationTest->order_id = $order->id;
                    $certificationTest->order_course_id = $course->id;
                    $certificationTest->status = 'Sin realizar';

                    if ($course_id == 6) {
                        $levelIndex = intdiv($i, 4);
                        $certLevels = ['BASICO', 'INTERMEDIO', 'AVANZADO'];
                        $certificationTest->description = $i % 4 < 3 ? "$certLevels[$levelIndex] " . (($i % 4) + 1) : "Ponderación $certLevels[$levelIndex]";
                        $certificationTest->enabled = true;
                        $certificationTest->premium = $i % 4 >= 3;
                    } else {
                        $certificationTest->description = $i < $limit - 1 ? "Examen de certificación " . ($i + 1) : "Ponderación";
                        $certificationTest->enabled = $i < 3;
                        $certificationTest->premium = ($i >= $limit - 1) || (in_array($course_id, $freeCourses) ? $i >= 3 : true);
                    }

                    $certificationTest->save();
                }
            }
        }

        return "Exito";
    }

    public function getSheetsData()
    {
        // Test
        // $sheets = [
        //     "1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8", // https://docs.google.com/spreadsheets/d/1CKiL-p7PhL2KxnfM7G2SXcffto7OGH7yM8BT3AiBWd8/edit#gid=810305363
        //     "1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo", // https://docs.google.com/spreadsheets/d/1vLB88xEriZVpMx7-xe960_0KrQm6l0795dMMafp_qLo/edit#gid=810305363
        //     "10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec", // https://docs.google.com/spreadsheets/d/10IYPXewqQL1WoVXx0b3vp-BOCbIBu0zZMVdbBAdSPec/edit#gid=810305363
        //     "1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA", // https://docs.google.com/spreadsheets/d/1GgPmMaJelAlH7V-ovHNKN9GQfqprE2Lq6eOFQfhGWNA/edit#gid=810305363
        // ];


        // Prod
        $sheets = [
            "1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI", // https://docs.google.com/spreadsheets/d/1_CBoJ5JyCjtMeOA1KIniWNqvDNxQUTDwMwV-qAYtedI/edit#gid=810305363
            "17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA", // https://docs.google.com/spreadsheets/d/17D-T9Gfs4DW4M-4TVabmWtuyosqrDaSuv7iH-Quc3eA/edit#gid=810305363
            "1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo", // https://docs.google.com/spreadsheets/d/1BCk_SHAD8sYjngCtGbi-0F65NJtF3nSS3n4gtcThaQo/edit#gid=810305363
            "14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs", // https://docs.google.com/spreadsheets/d/14v8gIrNdI3c3K1lEa8FYOyq6kOsw5gr0x8QTH2cbnUs/edit#gid=810305363
        ];


        $data = [];
        foreach ($sheets as $sheet) {
            $ranges = ['BASE!A1:ZZZ', 'CURSOS!A1:ZZZ'];

            $response = $this->service->spreadsheets_values->batchGet($sheet, ['ranges' => $ranges]);

            $baseSheet = $response[0]->getValues();
            $coursesSheet = $response[1]->getValues();


            $baseHeaders = $baseSheet[0];
            $baseData = [];
            array_shift($baseSheet); // Remove headers row (first row)
            foreach ($baseSheet as $row) {
                // Si hay más celdas en la fila que encabezados, elimina las celdas extra.
                $row = array_slice($row, 0, count($baseHeaders));

                // Si hay menos celdas en la fila que encabezados, añade valores vacíos hasta que tengan la misma cantidad.
                while (count($row) < count($baseHeaders)) {
                    $row[] = null;
                }

                $baseData[] = array_combine($baseHeaders, $row);  // Set headers as keys for each row
            }

            $coursesHeaders = $coursesSheet[0];
            $coursesData = [];
            array_shift($coursesSheet); // Remove headers row (first row)
            foreach ($coursesSheet as $row) {
                // Si hay más celdas en la fila que encabezados, elimina las celdas extra.
                $row = array_slice($row, 0, count($coursesHeaders));

                // Si hay menos celdas en la fila que encabezados, añade valores vacíos hasta que tengan la misma cantidad.
                while (count($row) < count($coursesHeaders)) {
                    $row[] = null;
                }

                $coursesData[] = array_combine($coursesHeaders, $row);  // Set headers as keys for each row
            }


            // merge base and courses data by CORREO
            $data = [];

            foreach ($baseData as $baseRow) {
                $email = $baseRow['CORREO'] ?? null;

                if ($email) {
                    foreach ($coursesData as $courseRow) {
                        if ($email == $courseRow['CORREO']) {
                            // Merge base and course rows for the same 'CORREO'
                            $data[] = array_merge($baseRow, $courseRow);
                        }
                    }
                }
            }
        }

        return $data;
    }



    public function formatCourses($data)
    {

        $courses_not_found = [];
        $courses_not_found = [];
        foreach ($data as $i => $student) {

            $courses_names = explode('+', $student['CURSOS']);
            $courses_names = array_map('trim', $courses_names);
            $courses_names = array_map('strtoupper', $courses_names);

            $courses = [];
            foreach ($courses_names as $course_name) {

                if (in_array($course_name, ['PP', 'MM', 'PM', 'HCM', 'INTEGRAL'])) {
                    $course_name = 'SAP ' . $course_name;
                }

                if (!$course_db = DB::table('courses')->where('short_name', $course_name)->when(isset($this->badCourses[$course_name]), function ($q) use ($course_name) {
                    return $q->orWhere('short_name', $this->badCourses[$course_name]);
                })->first()) {

                    $courses_not_found[] = [$this->badCourses[$course_name]];
                    continue;
                }

                if (strpos($course_name, 'SAP') !== false) {

                    $start = null;
                    $end = null;

                    $string = '';
                    if (strpos($student['ESTADO'], 'HABILITADO ') !== false) {
                        $string = 'HABILITADO ';
                    }
                    if (strpos($student['ESTADO'], 'AL DIA/ CUOTAS ') !== false) {
                        $string = 'AL DIA/ CUOTAS ';
                    }
                    if (strpos($student['ESTADO'], 'CONTADO ') !== false) {
                        $string = 'CONTADO ';
                    }

                    if ($string) {
                        $enable = explode($string, $student['ESTADO'])[1];
                        $enable = explode('/', $enable)[0];
                        $enable = explode(' PENDIENTE', $enable)[0];
                        $enable = explode(' ', $enable);
                        $enable = array_map(function ($item) {
                            return 'SAP ' . trim($item);
                        }, $enable);

                        if (in_array($course_name, $enable)) {
                            $start = $student['INICIO'];
                            $end = $student['FIN'];
                        }
                    } else {
                        $start = $student['INICIO'];
                        $end = $student['FIN'];
                    }

                    $courses[] = [
                        'course_id' => $course_db->id,
                        'sap_user'  => $student['USUARIO SAP'],
                        'start'     => $start ?  Carbon::createFromFormat('d/m/Y', $start)->format('Y-m-d') : null,
                        'end'       => $end ?  Carbon::createFromFormat('d/m/Y', $end)->format('Y-m-d') : null,
                        'type'      => 'paid'
                    ];
                }

                // If not include SAP courses
                if (strpos($course_name, 'SAP') === false) {
                    $dates = [
                        // Excel
                        6 => ['start' => 'EXC INICIO', 'end' => 'EXC FIN'],
                        // Fundamentos de Power BI
                        7 => ['start' => 'PBI INICIO', 'end' => 'PBI FIN'],
                        // Power BI para el Análisis de Datos
                        8 => ['start' => 'PBI INICIO', 'end' => 'PBI FIN'],
                        // Fundamentos de MS Project 2019
                        9 => ['start' => 'MSP INICIO', 'end' => 'MSP FIN'],
                    ];

                    try {
                        $courses[] = [
                            'course_id' => $course_db->id,
                            'sap_user'  => $student['USUARIO SAP'],
                            'start'     => $student[$dates[$course_db->id]['start']] ? Carbon::createFromFormat('d/m/Y', $student[$dates[$course_db->id]['start']])->format('Y-m-d') : null,
                            'end'       => $student[$dates[$course_db->id]['end']] ? Carbon::createFromFormat('d/m/Y', $student[$dates[$course_db->id]['end']])->format('Y-m-d') : null,
                            'type'      => 'free'
                        ];
                    } catch (\Throwable $th) {
                        $courses[] = [
                            'course_id' => $course_db->id,
                            'sap_user'  => $student['USUARIO SAP'],
                            'start'     => null,
                            'end'       => null,
                            'type'      => 'free'
                        ];
                    }
                }
            }

            $data[$i]['courses'] = $courses;
        }

        // $courses_not_found =  array_values(array_unique($courses_not_found));
        return $data;
    }

    public function formatPayment($data)
    {
        $allPayments = [];
        $currenciesDB = Currency::all()->pluck('id', 'iso_code')->toArray();

        $paymentMethods = [];
        foreach ($data as $key => $student) {
            $payments = [];
            $fields = ['RESERVA' => 'FECHA'];

            for ($i = 1; $i <= 6; $i++) {
                $fields['PAGO ' . $i] = 'FECHA ' . $i;
            }

            foreach ($fields as $paymentField => $dateField) {
                $paymentString = $student[$paymentField];

                $currency = preg_replace('/[0-9.,\s]+/', '', $paymentString);
                $currency = trim(strtoupper($currency));
                switch ($currency) {
                    case 'MEX':
                        $currency = 'MXN';
                        break;
                    case 'S/':
                        $currency = 'PEN';
                        break;
                }

                $amount = preg_replace('/[^0-9.]+/', '', $paymentString);
                $amount = floatval($amount);


                if (isset($currenciesDB[$currency]) && $amount && $currency) {
                    $payments[] = [
                        'currency_id' => $currenciesDB[$currency],
                        'amount'      => $amount,
                        'date'        => Carbon::createFromFormat('d/m/Y', $student[$dateField])->format('Y-m-d'),
                    ];
                }
            }

            if ($payments) {
                $allPayments[] = $payments;
            }
            $data[$key]['payments'] = $payments;
        }
        return $data;
    }






    public function createGoogleServiceInstance()
    {
        $client = new Google_Client();

        // Load credentials from the storage
        $credentialsPath = storage_path('app/public/credentials.json');

        if (file_exists($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
        } else {
            throw new Exception('Missing Google Service Account credentials file.');
        }

        $client->setApplicationName("Client_Library_Examples");
        $client->setScopes([
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/spreadsheets'
        ]);
        $client->setAccessType('offline');

        $this->service = new Google_Service_Sheets($client);
    }
}
