<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadTraking extends Model
{
    use HasFactory;

    protected $table = 'leads_traking';

    protected $fillable = [
        'user_id',
        'lead_id',
    ];
}
