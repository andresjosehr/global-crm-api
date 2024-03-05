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
        $user = $request->user();
        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;

        $data = LiveconnectMessagesLog::with('student')
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->whereHas('student', function ($query) use ($user) {
                    $studetnsIds = $user->studentsAssigned->pluck('id')->toArray();
                    $query->whereIn('id', $studetnsIds);
                });
            })
            ->when($request->date, function ($query) use ($request) {
                return $query->whereDate('created_at', $request->date);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return ApiResponseController::response('Exito', 200, $data);
    }

    public function getMailsList(Request $request)
    {
        $user = $request->user();
        $perPage = $request->input('perPage') ? $request->input('perPage') : 100;
        $data = ResendMailLog::with('student')
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->whereHas('student', function ($query) use ($user) {
                    $studetnsIds = $user->studentsAssigned->pluck('id')->toArray();
                    $query->whereIn('id', $studetnsIds);
                });
            })
            ->when($request->date, function ($query) use ($request) {
                return $query->whereDate('created_at', $request->date);
            })
            ->orderBy('id', 'desc')->paginate($perPage);

        return ApiResponseController::response('Exito', 200, $data);
    }
}
