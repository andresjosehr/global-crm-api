<?php

namespace App\Http\Services;

use App\Http\Controllers\NotificationController;
use App\Models\LiveconnectMessagesLog;
use App\Models\Student;
use App\Models\Token;
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
                'PageGearToken' => Token::where('service', 'liveconnect')->first()->token
            ]
        ]);

        return json_decode($res->getBody());
    }


    public function sendMessage($channel_id = 521, $phone, $message, $student_id = null, $trigger = 'SCHEDULED', $message_type = NULL, $tiggered_by = null)
    {

        if (env('APP_ENV') != 'production') {
            $phone = '584140339097';
        }

        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', $this->baseUrl . "/direct/wa/sendMessage", [
            'headers' => [
                'Content-Type' => 'application/json',
                'PageGearToken' => Token::where('service', 'liveconnect')->first()->token
            ],
            'json' => [
                'id_canal' => $channel_id,
                'numero'   => $phone,
                'mensaje'  => $message
            ]
        ]);

        $body = $res->getBody();

        Log::info([json_decode($body)]);
        if (json_decode($body)->status_message != 'Ok' && $student_id) {

            $student = Student::with('user')->where('id', $student_id)->first();
            $noti = new NotificationController();
            $noti = $noti->store([
                'title'      => 'Ha ocurrido un error enviando un mensaje automatico',
                'body'       => 'Alumno: ' . $student->name . ', Fecha y hora: ' . Carbon::now()->format('Y-m-d H:i:s') . ', Mensaje: ' . $message,
                'icon'       => 'check_circle_outline',
                'url'        => '#',
                'user_id'    => $student->users[0]->id,
                'use_router' => false,
                'custom_data' => []
            ]);
        }

        // stdClass Object to array
        LiveconnectMessagesLog::create([
            'channel_id'           => $channel_id,
            'phone'                => $phone,
            'message'              => $message,
            'trigger'              => $trigger,
            'student_id'           => $student_id,
            'message_type'         => $message_type,
            'tiggered_by'          => $tiggered_by,
            'liveconnect_response' => $res->getBody()
        ]);

        return json_decode($res->getBody());
    }
}
