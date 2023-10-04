<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Country;

class CountriesController extends Controller
{
    function index(){

        // order cities by name
        $countries = Country::with(['cities' => function($q){
            $q->orderBy('name');
        }])->orderBy('name')->get();

        return ApiResponseController::response('Consulta Exitosa', 200, $countries);
    }
}
