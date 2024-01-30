<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\State;

class CountriesController extends Controller
{
    public function index(){

        // order cities by name
        $countries = Country::orderBy('name')->get();

        return ApiResponseController::response('Consulta Exitosa', 200, $countries);
    }

    public function getStateByCountry($country_id){
        $state = Country::find($country_id)->states()->orderBy('name')->get();
        return ApiResponseController::response('Consulta Exitosa', 200, $state);
    }

    public function getCityByState($state_id){
        $city = State::find($state_id)->cities()->orderBy('name')->get();
        return ApiResponseController::response('Consulta Exitosa', 200, $city);
    }

    public function getCity($id){
        $city = City::find($id);
        return ApiResponseController::response('Consulta Exitosa', 200, $city);
    }

    public function getState($id){
        $state = State::find($id);
        return ApiResponseController::response('Consulta Exitosa', 200, $state);
    }
}
