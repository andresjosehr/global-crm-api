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

    static public function sendSigleMail($mail)
    {

        $resend = Resend::client(env('RESEND_API_KEY'));

        if (env('APP_ENV') != 'production') {
            $mail['to'] = ['delivered@resend.dev'];
        }
        $data = $resend->emails->send($mail);

        $mail['response'] = json_encode($data);
        $mail['to'] = implode(',', $mail['to']);
        $mail['status'] = 'Enviado';
        $resendMailLog = new ResendMailLog();
        $resendMailLog->fill($mail);
        $resendMailLog->save();

        return 'Yep';
    }

    static public function sendBatchMail($mails)
    {

        if (env('APP_ENV') != 'production') {
            $mails = array_map(function ($mail) {
                $mail['to'] = ['delivered@resend.dev'];
                return $mail;
            }, $mails);
        }

        $resend = Resend::client(env('RESEND_API_KEY'));
        // Get body

        // Chunk the array of mails into arrays of 100 mails
        $chukMails = array_chunk($mails, 100);

        foreach ($chukMails as $chunk) {
            $data = $resend->batch->send($chunk);


            $i = 0;
            foreach ($chunk as $mail) {
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
}
