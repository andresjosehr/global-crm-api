<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Wordpress\WpUser;
use App\Models\Wordpress\WpLearnpressUserItem;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'city_id',
        'state_id',
        'phone',
        'document_type_id',
        'document',
        'email',
        'lead_id',
        'user_id'
    ];

    protected $appends = ['start_date'];

    function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wp_user()
    {
        return $this->hasOne(WpUser::class, 'user_login', 'classroom_user');
    }

    public function wpLearnpressUserItems()
    {
        return $this->hasManyThrough(
            WpLearnpressUserItem::class,
            WpUser::class,
            'ID',  // Foreign key on UserWP table
            'user_id', // Foreign key on WpLearnpressUserItem table
            'classroom_user',  // Local key on Student table
            'ID'   // Local key on UserWP table
        );
        // ->where('item_type', 'lp_quiz')
        //     ->whereHas('course', function ($query) {
        //         $query->where('wp_post_id', 'ref_id');
        //     });
    }

    public function attachCertificationTest()
    {
        if (!$this->orders) {
            return $this;
        }

        foreach ($this->orders as $i => $order) {
            if (!$order->orderCourses || !$this->wp_user) {
                continue;
            }
            foreach ($order->orderCourses as $k => $order_course) {
                $this->orders[$i]->orderCourses[$k]->attachCertificationTestCourse($this->wp_user->ID);
            }
        }
        return $this;
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_student', 'student_id', 'user_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function sapInstalations()
    {
        return $this->hasMany(SapInstalation::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function userAssigned()
    {
        // The last user assigned to the student
        return $this->belongsToMany(User::class, 'user_student', 'student_id', 'user_id')->orderBy('user_student.created_at', 'desc')->limit(1);
    }

    public function getStartDateAttribute()
    {
        try {
            return $this->orders[0]->orderCourses[0]->start;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
