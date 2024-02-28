<?php

namespace App\Http\Services;

use App\Models\ZohoToken;
use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;

class ZohoService
{

    static public function createCalendarEvent($start, $end, $title, $attendees = [])
    {


        $token = ZohoToken::where('type', 'production')->first()->token;
        $calendar = 'bcc9a0f5ac0949b69b9a99ba331dd03f';
        if (env('APP_ENV') != 'production') {
            $token = ZohoToken::where('type', 'qa')->first()->token;
            $calendar = '70d8c9bc138b47ab8a8cd00de7a60b9b';

            $attendees = [];
        }



        $start = Carbon::parse($start)->addHours(5)->format('Ymd\THis\Z');
        $end = Carbon::parse($end)->addHours(5)->format('Ymd\THis\Z');


        // Log::info($attendees);
        $data = [
            "title" => $title,
            "dateandtime" => [
                "timezone" => "America/Lima",
                "start" => $start,
                "end" => $end,
            ]
        ];

        if (count($attendees) > 0) {
            $data['attendees'] = $attendees;
        }
        // return $data;
        $data = self::arrayToJson($data);
        Log::info($data);

        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', "https://calendar.zoho.com/api/v1/calendars/$calendar/events?eventdata=$data", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ]
        ]);

        return $res->getBody();
    }


    static public function arrayToJson($data)
    {
        if (is_array($data)) {
            $result = [];

            // Determinar si el arreglo es asociativo o secuencial
            $isAssoc = array_keys($data) !== range(0, count($data) - 1);

            foreach ($data as $key => $value) {
                $valueJson = self::arrayToJson($value); // Recursividad para sub-elementos
                $keyJson = $isAssoc ? '"' . addslashes($key) . '":' : '';
                $result[] = $keyJson . $valueJson;
            }

            $json = $isAssoc ? '{' : '[';
            $json .= implode(',', $result);
            $json .= $isAssoc ? '}' : ']';

            return $json;
        } elseif (is_string($data)) {
            return '"' . addslashes($data) . '"';
        } elseif (is_numeric($data)) {
            return $data;
        } elseif (is_bool($data)) {
            return $data ? 'true' : 'false';
        } elseif (is_null($data)) {
            return 'null';
        }

        // Añadir más casos según sea necesario
    }



    static public function deleteCalendarEvent($uid, $etag)
    {

        $token = ZohoToken::where('type', 'production')->first()->token;
        $calendar = 'bcc9a0f5ac0949b69b9a99ba331dd03f';
        if (env('APP_ENV') != 'production') {
            $token = ZohoToken::where('type', 'qa')->first()->token;
            $calendar = '70d8c9bc138b47ab8a8cd00de7a60b9b';
        }


        $client = new GuzzleHttp\Client();
        $res = $client->request('DELETE', "https://calendar.zoho.com/api/v1/calendars/$calendar/events/$uid", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token,
                "etag" => $etag
            ]
        ]);

        return $res->getBody();
    }
}
