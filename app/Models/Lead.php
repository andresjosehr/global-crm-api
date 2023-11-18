<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'courses',
        'phone',
    ];

    public function leadAssignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function observations()
    {
        return $this->hasMany(LeadObservation::class)->orderBy('created_at', 'desc');
    }
}
