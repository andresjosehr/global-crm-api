<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Holiday;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Price;
use App\Models\Role;
use Illuminate\Http\Request;

class OptionsController extends Controller
{
    public function getCurrencies()
    {
        return ApiResponseController::response('Exito', 200, Currency::with('paymentMethods')->get());
    }

    public function getScheduleSapPrices()
    {
        $prices = Price::where('description', 'Reagendamiento SAP')->get();

        return ApiResponseController::response('Exito', 200, $prices);
    }

    public function getPaymentMethods()
    {
        $methods = PaymentMethod::all();

        return ApiResponseController::response('Exito', 200, $methods);
    }

    public function getHolidays()
    {
        $holidays = Holiday::all();

        return ApiResponseController::response('Exito', 200, $holidays);
    }

    public function getRoles()
    {
        $roles = Role::all();

        return ApiResponseController::response('Exito', 200, $roles);
    }
}
