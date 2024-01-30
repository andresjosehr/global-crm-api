<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Freezing extends Model
{
    use HasFactory;

    protected $fillable = [
        'duration',
        'start_date',
        'finish_date',
        'return_date',
        'payment_date',
        'order_id',
        'remain_license',
        'order_course_id',
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
}


