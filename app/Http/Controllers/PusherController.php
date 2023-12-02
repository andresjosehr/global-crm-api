<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PusherController extends Controller
{
    public function callActivity(){
        event(new \App\Events\CallActivityEvent('Call Activity Event'));
        return [];
    }
}
