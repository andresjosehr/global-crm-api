<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $user = User::where('email', 'marieereu1520@gmail.com')->first();

        $key    = env('ZADARMA_KEY');
        $secret = env('ZADARMA_SECRET');

        $api = new Api($key, $secret);
        $sip = $api->getWebrtcKey($user->zadarma_id);
        // $sip->key


        $statistics = $api->getStatistics(
            '2023-02-01 00:00:00',
            '2023-02-09 23:59:59',
            null,
            '721',
        );


        return response()->json($statistics);



        // public function getStatistics(
        //     $start = null,
        //     $end = null,
        //     $sip = null,
        //     $costOnly = null,
        //     $type = null,
        //     $skip = null,
        //     $limit = null
        // )

        // return $data;
    }
}
