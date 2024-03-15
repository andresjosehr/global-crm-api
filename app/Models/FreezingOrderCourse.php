<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreezingOrderCourse extends Model
{
    use HasFactory;

    protected $table = 'freezings_order_course';

    protected static function booted()
    {
        static::created(function ($freezingOrderCourse) {
            OrderCourse::where('id', $freezingOrderCourse->order_course_id)->update(['last_freezing_id' => $freezingOrderCourse->freezing_id]);
        });

        static::deleting(function ($freezingOrderCourse) {
            $lastFreezingId = FreezingOrderCourse::where('order_course_id', $freezingOrderCourse->order_course_id)->latest()->first();
            OrderCourse::where('id', $freezingOrderCourse->order_course_id)->update(['last_freezing_id' => $lastFreezingId->freezing_id]);
        });
    }
}
