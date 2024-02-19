<?php

namespace App\Http\Services;

use App\Models\ZohoToken;
use Carbon\Carbon;
use GuzzleHttp;

class ZohoService
{

    static public function createCalendarEvent($start, $end, $title)
    {


        $token = ZohoToken::where('type', 'production')->first()->token;
        $calendar = '70d8c9bc138b47ab8a8cd00de7a60b9b';
        if (env('APP_ENV') != 'production') {
            $token = ZohoToken::where('type', 'qa')->first()->token;
            $calendar = '70d8c9bc138b47ab8a8cd00de7a60b9b';
        }



        $start = Carbon::parse($start)->addHours(5)->format('Ymd\THis\Z');
        $end = Carbon::parse($end)->addHours(5)->format('Ymd\THis\Z');

        $data = json_encode([
            "title" => $title,
            "dateandtime" => [
                "timezone" => "America/Lima",
                "start" => $start,
                "end" => $end,
            ],
        ], JSON_FORCE_OBJECT);
        // return $data;

        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', "https://calendar.zoho.com/api/v1/calendars/$calendar/events?eventdata=$data", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ]
        ]);

        return $res->getBody();
    }


    static public function deleteCalendarEvent($uid, $etag)
    {

        $token = ZohoToken::where('type', 'production')->first()->token;
        $calendar = '70d8c9bc138b47ab8a8cd00de7a60b9b';
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
