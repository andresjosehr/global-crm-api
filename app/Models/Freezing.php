<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freezing extends Model
{
    use HasFactory;

    protected $fillable = [
        'months',
        'start_date',
        'finish_date',
        'set',
        'due_id',

        'return_date',
        'payment_date',
        'order_id',
        'price_id',
        'price_amount',
        'payment_date',
        'currency_id',
        'payment_method_id',
        'remain_license',
        'order_course_id',
    ];

    protected $appends = [
        'order_course_ids',
    ];

    public function setFinishDateAttribute($value)
    {
        $this->attributes['finish_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setReturnDateAttribute($value)
    {
        $this->attributes['return_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function orderCourses()
    {
        // table freezings_order_course
        return $this->belongsToMany(OrderCourse::class, 'freezings_order_course');
    }

    public function due()
    {
        return $this->belongsTo(Due::class);
    }

    // ordercourse attribute
    public function getOrderCourseIdsAttribute()
    {
        return $this->orderCourses->pluck('id');
    }
}
