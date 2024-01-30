<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\OrderCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function orderCourse()
{
    return $this->belongsTo(OrderCourse::class);
}



}
