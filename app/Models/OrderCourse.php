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
        'certification_status',
        'classroom_status',
        'certification_status_excel_1',
        'certification_status_excel_2',
        'certification_status_excel_3',
        'observation',
        'welcome_mail_id',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['credly'];

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

    public function extensions()
    {
        return $this->belongsToMany(Extension::class, 'extensions_order_course', 'order_course_id', 'extension_id');
    }

    public function freezing()
    {
        return $this->hasMany(FreezingOrderCourse::class);
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
        try {
            //code...

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


            if (count($this->certificationTests) > 0) {
                if ($this->certificationTests[0]->orderCourse->course->id != 6) {
                    foreach ($wp_certification_tests as $key => $wp_certification_test) {


                        if ($this->certificationTests[$key]->description === 'Ponderación') {
                            continue;
                        }
                        $this->certificationTests[$key]->status = $wp_certification_test->graduation == 'passed' ? 'Aprobado' : 'Reprobado';
                        $this->certificationTests[$key]->start_time = $wp_certification_test->start_time;
                        $this->certificationTests[$key]->wp_certification = $wp_certification_test;
                    }
                }


                if ($this->certificationTests[0]->orderCourse->course->id == 6) {
                    $i = 0;

                    foreach ($wp_certification_tests as $key => $wp_certification_test) {

                        $this->certificationTests[$i]->status = $wp_certification_test->graduation == 'passed' ? 'Aprobado' : 'Reprobado';
                        $this->certificationTests[$i]->start_time = $wp_certification_test->start_time;
                        $this->certificationTests[$i]->wp_certification = $wp_certification_test;

                        if ($wp_certification_test->graduation == 'passed') {
                            if ($i < 4) {
                                $i = 4;
                                continue;
                            }

                            if ($i < 7) {
                                $i = 7;
                            }
                        }

                        $i++;
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
        }


        return $this;
    }

    public function attachLessonProgress()
    {

        if ($this->course->wp_post_id == 45661) {
            return $this;
        }

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

    public function getCredlyAttribute()
    {
        if ($this->type == 'paid') {
            return null;
        }

        $orderCourses = OrderCourse::where('type', 'paid')->where('certification_status', 'Emitido')->where('order_id', $this->order_id)->count();


        if ($orderCourses == 0) {
            return false;
        }

        if ($orderCourses > 0) {
            return true;
        }
    }
}
