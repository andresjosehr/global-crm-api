<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    function orderCourses()
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

    function invoice()
    {
        return $this->hasOne(Invoice::class);
    }


    function price(){
        return $this->belongsTo(Price::class);
    }

    // Accesor for terms_confirmed_by_student boolean
    public function getTermsConfirmedByStudentAttribute($value)
    {
        return $value == 1 ? true : false;
    }
}
