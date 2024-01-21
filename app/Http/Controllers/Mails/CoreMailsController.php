<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Models\ZohoToken;
use Illuminate\Http\Request;
use GuzzleHttp;

class CoreMailsController extends Controller
{
    static public function sendMail(
        $toAddress,
        $subject,
        $content,
        $scheduleTime = null,
    )
    {

        $body = [
            'fromAddress' => 'coordinacionacademica@globaltecnologiasacademy.com',
            'toAddress' => $toAddress,
            'subject' => $subject,
            'content' => $content,
            "mailFormat" => "html",
            "isSchedule" => true,
            "scheduleType" => "6",
            "timeZone" => "America/Lima",
            "scheduleTime" => $scheduleTime . " 00:10:00"
        ];

        if($scheduleTime == null){
            unset($body['isSchedule']);
            unset($body['scheduleType']);
            unset($body['timeZone']);
            unset($body['scheduleTime']);
        }

        $token = ZohoToken::where('token', '<>', '')->first()->token;
        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', 'https://mail.zoho.com/api/accounts/6271576000000008002/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ],
            'body' => json_encode($body)
        ]);
    }
}
