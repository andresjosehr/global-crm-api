<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;


    function courses()
    {
        return $this->belongsToMany(Course::class, 'courses_prices');
    }

    // currency
    function currency()
    {
        return $this->belongsTo(Currency::class);
    }

}
