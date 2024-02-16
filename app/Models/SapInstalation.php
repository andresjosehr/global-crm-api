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
        "key",
        "price_id",
        "price",
        "sap_user",
        "currency_id",
        'payment_enabled',
        "payment_method_id",
        "sap_payment_date",
        "staff_id",
        "screenshot",
        "draft",
        "observation",
    ];

    public function getTimeInsAttribute()
    {
        if (!$this->start_datetime) {
            return null;
        }
        return Carbon::parse($this->start_datetime)->format('H:i:s');
    }

    public function getDateInsAttribute()
    {
        if (!$this->start_datetime) {
            return null;
        }
        return Carbon::parse($this->start_datetime)->format('Y-m-d');
    }

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
}
