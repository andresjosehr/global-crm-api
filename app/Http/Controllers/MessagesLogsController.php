<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LiveconnectMessagesLog;
use App\Models\ResendMailLog;
use Illuminate\Http\Request;

class MessagesLogsController extends Controller
{
    public function getLiveConnectMessagesList(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;

        $data = LiveconnectMessagesLog::with('student')->orderBy('id', 'desc')->paginate($perPage);

        return ApiResponseController::response('Exito', 200, $data);
    }

    public function getMailsList(Request $request)
    {
        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;
        $data = ResendMailLog::with('student')->orderBy('id', 'desc')->paginate($perPage);

        return ApiResponseController::response('Exito', 200, $data);
    }
}
