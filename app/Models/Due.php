<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Due extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'order_id',
        'payment_method_id',
        'amount',
        'due_date',
        'status',
    ];
}
