<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;


    function prices()
    {
        return $this->belongsToMany(Price::class, 'course_prices', 'course_id', 'price_id');
    }
}
