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
        $scheduleTime,
    )
    {
        $token = ZohoToken::where('token', '<>', '')->first()->token;
        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', 'https://mail.zoho.com/api/accounts/6271576000000008002/messages', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ],
            'body' => json_encode([
                'fromAddress' => 'coordinacionacademica@globaltecnologiasacademy.com',
                'toAddress' => $toAddress,
                'subject' => $subject,
                'content' => $content,
                "mailFormat" => "html",
                "isSchedule" => true,
                "scheduleType" => "6",
                "timeZone" => "America/Lima",
                "scheduleTime" => $scheduleTime . " 00:10:00"
            ])
        ]);
    }
}
