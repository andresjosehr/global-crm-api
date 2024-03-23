<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class Order extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'payment_mode',
        'terms_confirmed_by_student',
        'price_amount',
        'student_id',
        'sap_notes',
        'created_by',
        'created_at',
        'updated_at',
    ];
    use HasFactory;

    function orderCourses()
    {
        return $this->hasMany(OrderCourse::class)->orderBy('start', 'asc');
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

    function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    function freezings()
    {
        return $this->hasManyThrough(Freezing::class, OrderCourse::class);
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

    public function sapInstalations()
    {
        return $this->hasMany(SapInstalation::class);
    }
}
