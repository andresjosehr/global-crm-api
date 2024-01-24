<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Due extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'date',
        'amount',
        'paid',
        'payment_method_id',
        'payment_method_id',
        'position',
    ];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
