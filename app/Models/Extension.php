<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\OrderCourse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class Extension extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        "id",
        "months",
        "order_id",
        "due_id",
        "order_course_id",
    ];

    protected static function booted()
    {

        static::created(function ($extension) {
            // create due
            $due = Due::create([
                'payment_reason' => 'Extension',
                'student_id' => $extension->orderCourse->order->student_id,
            ]);

            // update extension with due_id
            $extension->update(['due_id' => $due->id]);
        });

        static::deleting(function ($extension) {
            // delete due
            Due::where('id', $extension->due_id)->delete();
        });
    }


    public function setPaymentDateAttribute($value)
    {
        if ($value) {
            $this->attributes['payment_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function orderCourse()
    {
        return $this->belongsTo(OrderCourse::class);
    }

    public function due()
    {
        return $this->belongsTo(Due::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
