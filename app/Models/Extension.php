<?php

namespace App\Models;

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
        "price_id",
        "price_amount",
        "currency_id",
        "payment_method_id",
    ];
}
