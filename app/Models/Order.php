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

    function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    function invoice()
    {
        return $this->hasOne(Invoice::class);
    }


    function price()
    {
        return $this->belongsTo(Price::class);
    }

    // Accesor for terms_confirmed_by_student boolean
    public function getTermsConfirmedByStudentAttribute($value)
    {
        return $value == 1 ? true : false;
    }


    public function attachCertificationTest($user_id)
    {
        $student = Student::with('wp_user')->find($user_id);

        if (!$this->orderCourses || !$this->wp_user) {
            return $this;
        }
        foreach ($this->orderCourses as $k => $order_course) {
            $this->orderCourses[$k]->attachCertificationTestCourse($student->wp_user->ID);
        }
        return $this;
    }
}
