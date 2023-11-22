<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadObservation extends Model
{
    use HasFactory;

    protected $appends = ['date', 'time'];

    protected $fillable = [
        'user_id',
        'lead_id',
        'call_status',
        'observation',
        'lead_assignment_id',
        'schedule_call_datetime'
    ];

    public function getDateAttribute()
    {
        $date = Carbon::parse($this->schedule_call_datetime);
        return $date->format('Y-m-d');
    }

    public function getTimeAttribute()
    {
        $time = Carbon::parse($this->schedule_call_datetime);
        return $time->format('H:i:00');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leadAssignment()
    {
        return $this->belongsTo(LeadAssignment::class);
    }


}
