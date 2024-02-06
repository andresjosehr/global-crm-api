<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\ZohoToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;

class CoreMailsController extends Controller
{
    static public function sendMail(
        $toAddress,
        $subject,
        $content,
        $scheduleTime = null,
    ) {

        // Check if not in production
        if (env('APP_ENV') != 'production') {
            $toAddress = env('MAIL_DEBUG');
        }
        $body = [
            'fromAddress'  => 'coordinacionacademica@globaltecnologiasacademy.com',
            'toAddress'    => $toAddress,
            'subject'      => $subject,
            'content'      => $content,
            "mailFormat"   => "html",
            "isSchedule"   => true,
            "scheduleType" => "6",
            "timeZone"     => "America/Lima",
            "scheduleTime" => $scheduleTime . " 00:10:00"
        ];

        if ($scheduleTime == null) {
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

        $date = Carbon::now()->format('d-M-Y');

        $searchTerms = [
            'to'       => $toAddress,
            'in'       => $scheduleTime != null ? '6271576000000008028' : '6271576000000008022',
            // 'subject'  => $subject,
            'fromDate' => $date,
            'toDate'   => $date,
        ];

        sleep(20);

        $response = self::getMails($searchTerms);
        $mails = json_decode($response)->data;
        if (count($mails) > 1) {
            // Order by sentDateInGMT to get the last mail
            usort($mails, function ($a, $b) {
                return $a->sentDateInGMT < $b->sentDateInGMT;
            });
        }

        $lastMail = $mails[0];
        return $lastMail;
    }


    static public function getMails($parms = [])
    {
        $par = '';
        foreach ($parms as $key => $value) {
            $par .= $key . ':' . $value . '::';
        }
        if ($par != '') {
            $par = substr($par, 0, -2);
        }

        if ($par != '') {
            $par = '?searchKey=' . $par;
        }


        // return $par;

        $token = ZohoToken::where('token', '<>', '')->first()->token;
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', "https://mail.zoho.com/api/accounts/6271576000000008002/messages/search$par", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ]
        ]);

        return $res->getBody()->getContents();
    }

    static public function getFolders()
    {

        $token = ZohoToken::where('token', '<>', '')->first()->token;
        $client = new GuzzleHttp\Client();
        $res = $client->request('GET', 'https://mail.zoho.com/api/accounts/6271576000000008002/folders', [


            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ],
        ]);

        return $res->getBody()->getContents();
    }
}
