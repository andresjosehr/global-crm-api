<?php

namespace App\Models;

use Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class Due extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'order_id',
        'date',
        'amount',
        'paid',
        'payment_method_id',
        'position',
        'payment_receipt',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
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

    // Has currency through order
    public function currency()
    {
        return $this->hasOneThrough(Currency::class, Order::class, 'id', 'id', 'order_id', 'currency_id');
    }
}
