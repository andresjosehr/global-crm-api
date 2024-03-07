<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class SapInstalation extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    public $fillable = [
        "order_id",
        "operating_system",
        "pc_type",
        "status",
        "order_course_id",
        'previus_sap_instalation',
        "instalation_type",
        "key",
        "restrictions",
        "due_id",
        "sap_user",
        "last_sap_try_id",
        "screenshot",
        "draft",
        "observation",
    ];




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

    public function sapTries()
    {
        return $this->hasMany(SapTry::class, 'sap_instalation_id');
    }

    public function lastSapTry()
    {
        // return $this->hasOne(SapTry::class, 'sap_instalation_id')->latest();
        return $this->belongsTo(SapTry::class, 'last_sap_try_id');
    }

    public function staff()
    {
        return $this->hasOne(User::class, 'id', 'staff_id');
    }

    public function orderCourse()
    {
        return $this->hasOne(OrderCourse::class, 'id', 'order_course_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function due()
    {
        return $this->hasOne(Due::class, 'id', 'due_id');
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
