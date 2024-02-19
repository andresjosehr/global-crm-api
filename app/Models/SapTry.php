<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapTry extends Model
{
    use HasFactory;

    protected $fillable = [
        "start_datetime",
        "end_datetime",
        "start_datetime_target_timezone",
        "timezone",
        "staff_id",
        "status",
        "schedule_at",
    ];

    public function sapInstalation()
    {
        return $this->belongsTo(SapInstalation::class, 'sap_instalation_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
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
            \File::put(storage_path() . '/app/public/dues/' . $newFileName, base64_decode($file));
            $payment_receipt = $newFileName;
        }

        $this->attributes['payment_receipt'] = $payment_receipt;
    }
}
