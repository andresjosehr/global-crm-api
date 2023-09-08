<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Sheets;

class ImportContorller extends Controller
{

    private $service;

    public function index()
    {

        self::createGoogleServiceInstance();

        $data = self::getSheetsData();

        $users = [];
        foreach ($data as $row) {
            $users[] = [
                'name' => $row['NOMBRE COMPLETO CLIENTE'],
                'phone' => $row['TELÉFONO'] ? $row['TELÉFONO'] : $row['TELEFONO'],
                'document' => $row['DOCUMENTO'],
                'email' => $row['CORREO'],
            ];
        }

        // Truncate table
        \DB::table('students')->truncate();
        // insert
        \DB::table('students')->insert($users);

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
