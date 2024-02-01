<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Exception;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_Request;
use Google_Service_Sheets_CellData;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;
use Google_Service_Sheets_ExtendedValue;
use Illuminate\Support\Facades\Log;

class GoogleSheetController extends Controller
{
    public $service;
    protected $sheets;

    public function __construct()
    {
        $this->client = $this->initializeGoogleClient();
        $this->service = new Google_Service_Sheets($this->client);
    }

    private function initializeGoogleClient()
    {
        $client = new Google_Client();
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

        return $client;
    }


    /**
     * @param array $data An associative array representing the data to be updated.
     *                    The key is the sheet ID and the value is an array of updates.
     *                    Each update is an associative array with keys 'column', 'course_row_number', and 'value'.
     *
     * Structure:
     * [
     *
     *     'sheetId1' => [
     *         [
     *             'column' => 'A',
     *             'course_row_number' => 1,
     *             'value' => 'Completed'
     *         ],
     *         [
     *             'column' => 'B',
     *             'course_row_number' => 2,
     *             'value' => 'In Progress'
     *         ]
     *     ],
     *     'sheetId2' => [
     *         [
     *             'column' => 'C',
     *             'course_row_number' => 3,
     *             'value' => 'Not Started'
     *         ]
     *     ]
     * ]
     */
    public function prepareRequests($data)
    {
        $requests = [];
        foreach ($data as $sheet) {
            $requests[$sheet['sheet_id']] = [];
            foreach ($sheet['tabs'] as $tab) {
                foreach ($tab['updates'] as $update) {
                    // Crear la estructura de datos para la celda
                    $cellData = new Google_Service_Sheets_CellData();

                    // Establecer el valor de la celda
                    $cellData->setUserEnteredValue(new Google_Service_Sheets_ExtendedValue(['stringValue' => $update['value']]));

                    // Verificar si hay una nota para agregar y agregarla si existe
                    if (isset($update['note'])) {
                        $cellData->setNote($update['note']);
                    }

                    // Crear la solicitud para actualizar la celda
                    $requests[$sheet['sheet_id']][] = new Google_Service_Sheets_Request([
                        'updateCells' => [
                            'range' => [
                                'sheetId'          => $tab['tab_id'],
                                'startRowIndex'    => $update['course_row_number'] - 1,
                                'endRowIndex'      => $update['course_row_number'],
                                'startColumnIndex' => $this->columnLetterToNumber($update['column']),
                                'endColumnIndex'   => $this->columnLetterToNumber($update['column']) + 1,
                            ],
                            'rows' => [
                                ['values' => [$cellData]]
                            ],
                            'fields' => 'userEnteredValue,note'
                        ]
                    ]);
                }
            }
        }
        return $requests;
    }


    public function transformData($originalData)
    {
        $groupedData = [];

        foreach ($originalData as $item) {
            $sheetId = $item['sheet_id'];
            $tabId = $item['tab_id'];

            // Inicializar 'sheet_id' si no existe
            if (!isset($groupedData[$sheetId])) {
                $groupedData[$sheetId] = [
                    'sheet_id' => $sheetId,
                    'tabs' => []
                ];
            }

            // Inicializar 'tab_id' si no existe
            if (!isset($groupedData[$sheetId]['tabs'][$tabId])) {
                $groupedData[$sheetId]['tabs'][$tabId] = [
                    'tab_id' => $tabId,
                    'updates' => []
                ];
            }

            $groupedData[$sheetId]['tabs'][$tabId]['updates'][] = [
                'column' => $item['column'],  // Puedes cambiar esto según tus necesidades
                'course_row_number' => $item['course_row_number'],
                'value' => $item['value'],
                'note' => isset($item['note']) ? $item['note'] : null
            ];
        }

        // Convertir la estructura de datos agrupada a la estructura de salida deseada
        $output = [];
        foreach ($groupedData as $sheet) {
            $sheetOutput = [
                'sheet_id' => $sheet['sheet_id'],
                'tabs' => []
            ];
            foreach ($sheet['tabs'] as $tab) {
                $sheetOutput['tabs'][] = $tab;
            }
            $output[] = $sheetOutput;
        }

        return $output;
    }

    public function updateGoogleSheet($requests)
    {
        $responses = [];
        foreach ($requests as $sheet_id => $updates) {

            $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $updates]);
            $response = $this->service->spreadsheets->batchUpdate($sheet_id, $batchUpdateRequest);
            $responses[] = [
                'sheet_id' => $sheet_id,
                'response' => $response
            ];
        }
        return $responses;
    }

    public function columnLetterToNumber($columnLabel)
    {
        $number = 0;
        $length = strlen($columnLabel);
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord(strtoupper($columnLabel[$i])) - ord('A') + 1);
        }
        return $number - 1;  // Restar 1 porque las columnas en Google Sheets comienzan desde 0
    }
}
