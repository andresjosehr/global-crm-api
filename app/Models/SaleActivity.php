<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleActivity extends Model
{
    use HasFactory;

    protected $table = 'sales_activities';

    protected $fillable = [
        'start',
        'end',
        'user_id',
        'lead_id',
        'type',
        'lead_assignment_id',
    ];
}
