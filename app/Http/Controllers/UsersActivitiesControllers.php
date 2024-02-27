<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class UsersActivitiesControllers extends Controller
{
    public function index(Request $request)
    {

        $user = $request->user();
        $perPage = $request->input('perPage') ? $request->input('perPage') : 10;
        $data = UserActivity::with('user')
            ->when($user->role_id != 1, function ($query) use ($user) {
                return $query->where('user_id', $user->id);
            })
            ->when($user->role_id == 1 && $request->user_id, function ($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return ApiResponseController::response('Success', 200, $data);
    }
}
