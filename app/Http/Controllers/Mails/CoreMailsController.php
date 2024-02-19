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
    static public function sendMail($toAddress, $subject, $content, $scheduleTime = null)
    {




        // Log::info('1');
        $token = ZohoToken::where('type', 'production')->first()->token;
        $fromAddress = 'coordinacionacademica@globaltecnologiasacademy.com';
        $accountId = '6271576000000008002';


        // Check if not in production
        if (env('APP_ENV') != 'production') {
            $toAddress = 'areacomercial@globaltecnologiasacademy.com';
            $subject = "PRUEBA | $subject";
            $token = ZohoToken::where('type', 'qa')->first()->token;
            $fromAddress = 'areacomercial@globaltecnologiasacademy.com';
            $accountId = '153623000000008002';
        }
        // Log::info('3');

        $body = [
            'fromAddress'  => $fromAddress,
            'toAddress'    => $toAddress,
            'subject'      => $subject,
            'content'      => $content,
            "mailFormat"   => "html",
            "isSchedule"   => true,
            "scheduleType" => "6",
            "timeZone"     => "America/Lima",
            "scheduleTime" => $scheduleTime . " 00:10:00"
        ];

        // Log::info('4');
        if ($scheduleTime == null) {
            unset($body['isSchedule']);
            unset($body['scheduleType']);
            unset($body['timeZone']);
            unset($body['scheduleTime']);
        }

        // Log::info('5');




        $client = new GuzzleHttp\Client();
        $res = $client->request('POST', "https://mail.zoho.com/api/accounts/$accountId/messages", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Zoho-oauthtoken ' . $token
            ],
            'body' => json_encode($body)
        ]);

        // Log::info('6');


        if (env('APP_ENV') != 'production') {
            return (object)[
                'messageId' => "XXXXXXXXX",
            ];
        }

        // Log::info('7');

        $date = Carbon::now()->format('d-M-Y');

        $searchTerms = [
            'to'       => $toAddress,
            'in'       => $scheduleTime != null ? '6271576000000008028' : '6271576000000008022',
            // 'subject'  => $subject,
            'fromDate' => $date,
            'toDate'   => $date,
        ];

        // Log::info('8');

        sleep(20);

        // Log::info('9');

        $response = self::getMails($searchTerms);
        $mails = json_decode($response)->data;
        if (count($mails) > 1) {
            // Order by sentDateInGMT to get the last mail
            usort($mails, function ($a, $b) {
                return $a->sentDateInGMT < $b->sentDateInGMT;
            });
        }

        // Log::info('10');

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



        $token = ZohoToken::where('type', 'production')->first()->token;

        if (env('APP_ENV') != 'production') {
            $token = ZohoToken::where('type', 'qa')->first()->token;
        }

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


        $token = ZohoToken::where('type', 'production')->first()->token;

        if (env('APP_ENV') != 'production') {
            $token = ZohoToken::where('type', 'qa')->first()->token;
        }


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
