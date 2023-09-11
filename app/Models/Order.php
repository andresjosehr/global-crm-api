<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    function courses()
    {
        return $this->hasMany(OrderCourse::class);
    }

    function student()
    {
        return $this->belongsTo(Student::class);
    }

    function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    function dues()
    {
        return $this->hasMany(Due::class);
    }

    function user(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
