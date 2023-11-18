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

    public function assignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }
}
