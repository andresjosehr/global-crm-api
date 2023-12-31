<?php

namespace App\Models;

use Carbon\Carbon;
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
        'previus_sap_instalation',
        "instalation_type",
        "price_id",
        "price",
        "sap_user",
        "currency_id",
        'payment_enabled',
        "payment_method_id",
        "sap_payment_date",
        "staff_id",
        "observation",
    ];

    public function getTimeAttribute()
    {
        return Carbon::parse($this->start_datetime)->format('H:i:s');
    }

    public function getDateAttribute()
    {
        return Carbon::parse($this->start_datetime)->format('Y-m-d');
    }

    public function getPaymentEnabledAttribute($value)
    {
        return $value ? true : false;
    }

    // Accesor for terms_confirmed_by_student boolean
    public function getPreviusSapInstalationAttribute($value)
    {
        return $value == 1 ? true : false;
    }
}
