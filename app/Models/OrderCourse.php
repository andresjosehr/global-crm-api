<?php

namespace App\Models;

use App\Models\Wordpress\WpLearnpressUserItem;
use App\Models\Wordpress\WpUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class OrderCourse extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'course_id',
        'order_id',
        'license',
        'course_type',
        'start',
        'type',
        'enabled',
        'end',
        'last_freezing_id',
        'classroom_status',
        'observation',
        'welcome_mail_id',
        'created_at',
        'updated_at',
    ];

    function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function certificationTests()
    {
        return $this->hasMany(CertificationTest::class);
    }

    public function freezings()
    {
        return $this->belongsToMany(Freezing::class, 'freezings_order_course', 'order_course_id', 'freezing_id');
    }

    public function freezing()
    {
        return $this->hasMany(FreezingOrderCourse::class);
    }

    public function extensions()
    {
        return $this->hasMany(Extension::class);
    }

    public function sapInstalations()
    {
        return $this->hasMany(SapInstalation::class);
        // ->select(['sap_instalations.*', DB::raw('TIME(sap_instalations.start_datetime) as time'), DB::raw('DATE(sap_instalations.start_datetime) as date')]);
    }

    public function dateHistory()
    {
        return $this->hasMany(DatesHistory::class);
    }


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Has one through order
    public function student()
    {
        return $this->hasOneThrough(Student::class, Order::class, 'id', 'id', 'order_id', 'student_id');
    }


    public function setStartAttribute($value)
    {
        if ($value) {
            $this->attributes['start'] = Carbon::parse($value)->format('Y-m-d');
        }
    }

    public function setEndAttribute($value)
    {
        if ($value) {
            $this->attributes['end'] = Carbon::parse($value)->format('Y-m-d');
        }
    }


    public function attachCertificationTestCourse()
    {
        $user = WpUser::where('user_email', $this->order->student->email)->first();

        if (!$user) {
            return $this;
        }

        Log::info([$user->ID, $this->course->wp_post_id]);

        $wp_certification_tests = WpLearnpressUserItem::whereHas('item', function ($q) {
            $q->where('post_title', 'LIKE', '%Certificación%');
        })
            ->where('user_id', $user->ID)
            ->where('ref_id', $this->course->wp_post_id)
            ->where('item_type', 'lp_quiz')
            ->orderBy('start_time', 'ASC')
            ->get();


        foreach ($wp_certification_tests as $key => $wp_certification_test) {
            $this->certificationTests[$key]->status = $wp_certification_test->graduation == 'passed' ? 'Aprobado' : 'Reprobado';
            $this->certificationTests[$key]->start_time = $wp_certification_test->start_time;
            $this->certificationTests[$key]->wp_certification = $wp_certification_test;
        }

        return $this;
    }

    public function attachLessonProgress()
    {

        $lessons = DB::connection('wordpress')->table('posts as lessons')
            ->select('lessons.*', 'sections.section_course_id', 'sections.section_name')
            ->join('learnpress_section_items as section_items', 'section_items.item_id', '=', 'lessons.ID')
            ->join('learnpress_sections as sections', 'sections.section_id', '=', 'section_items.section_id')
            ->where('lessons.post_type', 'lp_lesson')
            ->where('lessons.post_title', 'not like', '%webinar%')
            ->orderBy('sections.section_course_id')
            ->orderBy('sections.section_name')
            ->orderBy('section_items.item_order', 'ASC')
            ->get()->unique('ID')->values();

        $groupedLessons = $lessons->groupBy('section_course_id')->map(function ($group) {
            return $group->count();
        });

        $student = Student::where('email', $this->order->student->email)->with(['wpLearnpressUserItems' => function ($q) {
            $q->whereItemType('lp_lesson')->whereStatus('completed')->whereRefId($this->course->wp_post_id);
        }])->first();

        $student->wpLearnpressUserItems->count();

        $this->lesson_progress = $student->wpLearnpressUserItems->count() / $groupedLessons[$this->course->wp_post_id] * 100;

        // fix to 2 decimals
        $this->lesson_progress = number_format($this->lesson_progress, 2);

        // if greater than 100, set to 100
        if ($this->lesson_progress > 100) {
            $this->lesson_progress = 100;
        }

        return $this;
    }
}
