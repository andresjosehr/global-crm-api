<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapInstalation extends Model
{
    use HasFactory;

    public $fillable = [
        "id",
        "order_id",
        "start_datetime",
        "end_datetime",
        "operating_system",
        "pc_type",
        "status",
        "order_course_id",
        "instalation_type",
        "price_id",
        "price",
        "currency_id",
        "payment_method_id",
        "sap_payment_date",
        "staff_id",
        "observation",
    ];
}
