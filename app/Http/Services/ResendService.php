<?php

namespace App\Http\Services;

use App\Models\ResendMailLog;
use App\Models\ZohoToken;
use Carbon\Carbon;
use GuzzleHttp;
use Illuminate\Support\Facades\Log;
use Resend;

class ResendService
{

    static public function sendSigleMail()
    {

        $resend = Resend::client(env('RESEND_API_KEY'));

        return $resend->batch->send([
            [
                'from' => 'No contestar <noreply@globaltecnoacademy.com>',
                'to' => ['andresjosehr@gmail.com'],
                'subject' => 'hello world',
                'html' => '<h1>it works!</h1>',
            ],
            [
                'from' => 'No contestar <noreply@globaltecnoacademy.com>',
                'to' => ['interlinevzla@gmail.com'],
                'subject' => 'world hello',
                'html' => '<p>it works!</p>',
            ]
        ]);
    }

    static public function sendBatchMail($mails)
    {

        if (env('APP_ENV') != 'production') {
            $mails = array_map(function ($mail) {
                $mail['to'] = ['andresjosehr@gmail.com'];
                return $mail;
            }, $mails);
        }

        $resend = Resend::client(env('RESEND_API_KEY'));

        $data = $resend->batch->send($mails);

        $i = 0;
        foreach ($mails as $mail) {
            $mails[$i]['to'] = implode(',', $mail['to']);
            $mails[$i]['response'] = json_encode($data['data'][$i]);
            $mails[$i]['status'] = 'Enviado';

            $resendMailLog = new ResendMailLog();
            $resendMailLog->fill($mails[$i]);
            $resendMailLog->save();
            $i++;
        }

        return 'Yep';
    }
}
