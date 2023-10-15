<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatesHistory extends Model
{
    use HasFactory;

    protected $table = 'dates_history';

    protected $fillable = [
        'order_course_id',
        'order_id',
        'start_date',
        'end_date',
        'type',
        'extension_id',
        'freezing_id',
    ];

    function course()
    {
        return $this->hasOneThrough(
            Course::class,
            OrderCourse::class,
            'id',
            'id',
            'order_course_id',
            'course_id'
        );
    }

    public function setStartDateAttribute($value)
    {
        if ($value) {
            $this->attributes['start_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function setEndDateAttribute($value)
    {
        if ($value) {
            $this->attributes['end_date'] = Carbon::parse($value)->format('Y-m-d');
        }
    }
}
