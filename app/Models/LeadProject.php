<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_lead_projects');
    }
}
