<?php

namespace App\Http\Services;

use App\Models\LiveconnectMessagesLog;
use App\Models\ZohoToken;
use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;

class LiveConnectService
{
    private $baseUrl = 'https://api.liveconnect.chat/prod';

    public function getToken()
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseUrl . "/account/token", [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'cKey' => env('LIVE_CONNECT_PUBLIC_KEY'),
                'privateKey' => env('LIVE_CONNECT_PRIVATE_KEY'),
            ]
        ]);

        return json_decode($res->getBody());
    }

    public function getChannelsList()
    {
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', $this->baseUrl . "/channels/list", [
            'headers' => [
                'Content-Type' => 'application/json',
                'PageGearToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjX2tleSI6IjNkOTg4OGQ0YTcyNmI3NzM4YjM3YjA4ODAxYWFkZTU1IiwiaWRfcGdlIjo0MjEsImlkX2N1ZW50YSI6MTE0NCwibm9tYnJlIjoiR0FDQUFNIEdMT0JBTCBURUNOT0xPR0lBUyBBQ0FERU1ZIFNBQyIsImlhdCI6MTcwOTI1NTAwNCwiZXhwIjoxNzA5MjgzODA0fQ.-CTEv8lviOvr1HGtSiNK6rw3m3ALtXyT5h20kvk3sZk'
            ]
        ]);

        return json_decode($res->getBody());
    }


    public function sendMessage($channel_id, $phone, $message, $trigg)
    {

        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseUrl . "/direct/wa/sendMessage", [
            'headers' => [
                'Content-Type' => 'application/json',
                'PageGearToken' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjX2tleSI6IjNkOTg4OGQ0YTcyNmI3NzM4YjM3YjA4ODAxYWFkZTU1IiwiaWRfcGdlIjo0MjEsImlkX2N1ZW50YSI6MTE0NCwibm9tYnJlIjoiR0FDQUFNIEdMT0JBTCBURUNOT0xPR0lBUyBBQ0FERU1ZIFNBQyIsImlhdCI6MTcwOTMzMTk1NCwiZXhwIjoxNzA5MzYwNzU0fQ.o4PKu6ZafBgvVKR9-bZad1UDK7hdY7BQvC1HXyc2HVA'
            ],
            'json' => [
                'id_canal' => $channel_id,
                'numero'   => $phone,
                'mensaje'  => $message
            ]
        ]);

        LiveconnectMessagesLog::create([
            'channel_id' => $channel_id,
            'phone' => $phone,
            'message' => $message,
            'trigger' => $trigg,
            'message_type' => 'whatsapp',
            'tiggered_by' => auth()->user()->id,
            'liveconnect_response' => json_decode($res->getBody())
        ]);

        return json_decode($res->getBody());
    }
}
