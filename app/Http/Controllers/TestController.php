<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\ZadarmaStatistic;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Zadarma_API\Api;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Guzzle
        $refreshToken = env('ZOHO_TEST_REFRESH_TOKEN');
        $client_id = env('ZOHO_TEST_CLIENT_ID');
        $client_secret = env('ZOHO_TEST_CLIENT_SECRET');
        $grant_type = 'refresh_token';
        $redirect_uri = 'https://qa-api.mygisselle.com';
        $scope = 'ZohoMail.messages.ALL ZohoMail.accounts.ALL';

        $client = new \GuzzleHttp\Client();
        $res = $client->request('POST', "https://accounts.zoho.com/oauth/v2/token?refresh_token=$refreshToken&client_id=$client_id&client_secret=$client_secret&grant_type=$grant_type&redirect_uri=$redirect_uri&scope=$scope", [
            'body' => json_encode([
                'fromAddress' => 'areacomercial@globaltecnologiasacademy.com',
                'toAddress' => 'andresjosehr@gmail.com',
                'subject' => 'Custom datetime at!',
                'content' => 'Hola mundo!'
            ])
        ]);

        return $res->getBody();
    }
    public function importStatistics()
    {



        return "Exito";
    }
}
