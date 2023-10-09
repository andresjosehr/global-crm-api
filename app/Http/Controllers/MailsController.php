<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailsController extends Controller
{
    public function getToken(Request $request)
    {
        return DB::connection('wordpress')->table('zoho_token')->first()->token;
    }

    public function index(Request $request)
    {
        return view('mails.freezing');
    }


    public function scheduleFreezeEmail($freezingID){
        // return view
        return view('mails.freezing', [
            'freezingID' => $freezingID
        ]);
    }
}
