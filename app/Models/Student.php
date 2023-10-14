<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'city_id',
        'phone',
        'document_type_id',
        'document',
        'email',
    ];

    function orders()
    {
        return $this->hasMany(Order::class);
    }
}
