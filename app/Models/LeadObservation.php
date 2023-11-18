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
        'observation'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
