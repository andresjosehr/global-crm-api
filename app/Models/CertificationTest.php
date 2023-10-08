<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CertificationTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'status',
        'enabled',
        'premium',
        'average',
        'order_id',
        'order_course_id',
        'price_id',
        'price',
        'currency_id',
        'payment_method_id',
    ];
}
