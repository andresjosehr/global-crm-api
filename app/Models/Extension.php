<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use HasFactory;

    protected $fillable = [
        "id",
        "months",
        "order_id",
        "order_course_id",
        "payment_date",
        "price_id",
        "price_amount",
        "currency_id",
        "payment_method_id",
    ];


    public function setPaymentDateAttribute($value)
    {
        if($value){
            $this->attributes['payment_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }


}
