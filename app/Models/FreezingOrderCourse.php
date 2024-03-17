<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreezingOrderCourse extends Model
{
    use HasFactory;

    protected $table = 'freezings_order_course';

    protected $fillable = [
        'order_id',
        'order_course_id',
        'freezing_id',
    ];
}
