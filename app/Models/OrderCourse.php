<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'order_id',
        'license',
        'course_type',
        'start',
        'enabled',
        'end',
        'observation',
    ];

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function certificationTests()
    {
        return $this->hasMany(CertificationTest::class);
    }

    public function setStartAttribute($value)
    {
        $this->attributes['start'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setEndAttribute($value)
    {
        $this->attributes['end'] = Carbon::parse($value)->format('Y-m-d');
    }
}
