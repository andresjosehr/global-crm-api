<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function getAvailableTimes(Request $request, $id){
        $user = User::where('id', $id)->first();

        $availableTimes = $user->getAvailableTimesForDate($request->date, $request->datesBussy); // Reemplaza la fecha con la que deseas trabajar.

        return ApiResponseController::response('Consulta Exitosa', 200, $availableTimes);
    }
}
