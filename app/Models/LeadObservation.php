<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'call_status',
        'observation',
        'lead_assignment_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leadAssignment()
    {
        return $this->belongsTo(LeadAssignment::class);
    }


}
