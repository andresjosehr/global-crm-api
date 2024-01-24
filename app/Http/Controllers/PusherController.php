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

    public function sendNotification(){
        event(new \App\Events\SendNotificationEvent(8, ['title' => 'Test', 'body' => 'Test body']));
        return [];
    }
}
