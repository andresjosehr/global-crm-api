<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;

class CountriesController extends Controller
{
    function index(){
        return ApiResponseController::response('Consulta Exitosa', 200, Country::all());
    }
}
