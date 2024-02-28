<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class SapInstalation extends Model
{
    use HasFactory;

    public $fillable = [
        "order_id",
        "operating_system",
        "pc_type",
        "status",
        "order_course_id",
        'previus_sap_instalation',
        "instalation_type",
        "key",
        "price_id",
        "restrictions",
        "price_amount",
        "sap_user",
        "currency_id",
        'payment_enabled',
        "payment_method_id",
        "payment_date",
        "screenshot",
        'payment_receipt',
        "draft",
        "observation",
    ];

    protected $appends = [
        'start_datetime',
        'start_datetime_target_timezone',
        'end_datetime',
        'timezone',
        'time',
        'date',
        'staff_id',
        'last_try_status',
    ];

    public $payment_fields = [
        'price_id',
        'payment_date',
        'price_amount',
        'currency_id',
        'payment_receipt',
        'payment_method_id',
    ];


    public function getPaymentEnabledAttribute($value)
    {
        return $value ? true : false;
    }

    // Accesor for terms_confirmed_by_student boolean
    public function getPreviusSapInstalationAttribute($value)
    {
        if ($value === 1) {
            return true;
        }
        if ($value === 0) {
            return false;
        }
        if ($value === null) {
            return null;
        }
    }

    // User through order
    public function student()
    {
        return $this->hasOneThrough(Student::class, Order::class, 'id', 'id', 'order_id', 'student_id');
    }


    public function setScreenshotAttribute($screenshot)
    {

        $base64Types = [
            'data:image/jpeg;base64' => 'jpeg',
            'data:image/png;base64' => 'png',
            'data:application/pdf;base64' => 'pdf',
        ];


        $firstPart = explode(',', $screenshot)[0];
        if ($screenshot && isset($base64Types[$firstPart])) {

            $extension = $base64Types[$firstPart];

            $file = $screenshot;
            $file = str_replace($firstPart . ',', '', $file);
            $file = str_replace(' ', '+', $file);
            $date = date('Y-m-d-H-i-s');
            $newFileName = 'screenshot_' . Carbon::now()->format('Y-m-d-H-i-s') . '.' . $extension;
            \File::put(storage_path() . '/app/public/sap/' . $newFileName, base64_decode($file));
            $screenshot = $newFileName;
        }


        $this->attributes['screenshot'] = $screenshot;
    }

    public function sapTries()
    {
        return $this->hasMany(SapTry::class, 'sap_instalation_id');
    }

    public function lastSapTry()
    {
        return $this->hasOne(SapTry::class, 'sap_instalation_id')->latest();
        // return $this->belongsTo(SapTry::class, 'last_sap_try_id');
    }

    // add datetime attribute as property from last try
    public function getStartDatetimeAttribute()
    {
        $start = $this->sapTries->last();
        return $start ? Carbon::parse($start->start_datetime)->format('Y-m-d H:i:s') : null;
    }

    public function getStartDatetimeTargetTimezoneAttribute()
    {
        $start = $this->sapTries->last();
        return $start ? Carbon::parse($start->start_datetime_target_timezone)->format('Y-m-d H:i:s') : null;
    }

    public function getEndDatetimeAttribute()
    {
        $start = $this->sapTries->last();
        return $start ? Carbon::parse($start->end_datetime)->format('Y-m-d H:i:s') : null;
    }

    public function getStaffIdAttribute()
    {
        return $this->sapTries->last() ? $this->sapTries->last()->staff_id : null;
    }

    public function getTimeAttribute()
    {
        return $this->sapTries->last() ? Carbon::parse($this->sapTries->last()->start_datetime)->format('H:i') . ':00' : null;
    }

    public function getDateAttribute()
    {
        return $this->sapTries->last() ? Carbon::parse($this->sapTries->last()->start_datetime)->format('Y-m-d') : null;
    }

    public function getTimezoneAttribute()
    {
        return $this->sapTries->last() ? $this->sapTries->last()->timezone : null;
    }

    public function staff()
    {
        return $this->hasOne(User::class, 'id', 'staff_id');
    }

    public function getLastTryStatusAttribute()
    {
        return $this->sapTries->last() ? $this->sapTries->last()->status : null;
    }


    public function orderCourse()
    {
        return $this->hasOne(OrderCourse::class, 'id', 'order_course_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }




    public function setPaymentReceiptAttribute($payment_receipt)
    {

        $base64Types = [
            'data:image/jpeg;base64' => 'jpeg',
            'data:image/png;base64' => 'png',
            'data:application/pdf;base64' => 'pdf',
        ];


        $firstPart = explode(',', $payment_receipt)[0];
        if ($payment_receipt && isset($base64Types[$firstPart])) {

            $extension = $base64Types[$firstPart];

            $file = $payment_receipt;
            $file = str_replace($firstPart . ',', '', $file);
            $file = str_replace(' ', '+', $file);
            $date = date('Y-m-d-H-i-s');
            $newFileName = 'payment_receipt_' . Carbon::now()->format('Y-m-d-H-i-s') . '.' . $extension;
            \File::put(storage_path() . '/app/public/payment_receipts/' . $newFileName, base64_decode($file));
            $payment_receipt = $newFileName;
        }

        $this->attributes['payment_receipt'] = $payment_receipt;
    }


    public function setPaymentDateAttribute($payment_date)
    {
        $this->attributes['payment_date'] = Carbon::parse($payment_date)->format('Y-m-d');
    }
}
