<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use \OwenIt\Auditing\Auditable as AuditableTrait;

class SapTry extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        "start_datetime",
        "end_datetime",
        "start_datetime_target_timezone",
        "timezone",
        "staff_id",
        "status",
        "schedule_at",
        'link_sent_by',
    ];

    protected $appends = [
        'time',
        'date',
    ];

    protected static function booted()
    {
        static::created(function ($sapTry) {
            $sapTry->sapInstalation->update(['last_sap_try_id' => $sapTry->id]);
        });

        static::deleting(function ($sapTry) {
            // Set the last sap try id to last sap try id of the sap instalation
            $sapTry->sapInstalation->update(['last_sap_try_id' => $sapTry->sapInstalation->sapTries()->where('id', '!=', $sapTry->id)->latest()->first()->id]);
        });
    }

    public function sapInstalation()
    {
        return $this->belongsTo(SapInstalation::class, 'sap_instalation_id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function linkSentBy()
    {
        return $this->belongsTo(User::class, 'link_sent_by');
    }


    public function getTimeAttribute()
    {
        return Carbon::parse($this->start_datetime)->format('H:i') . ':00';
    }

    public function getDateAttribute()
    {
        return Carbon::parse($this->start_datetime)->format('Y-m-d');
    }
}
