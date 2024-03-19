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
        'due_id',
        'premium',
        'average',
        'order_id',
        'order_course_id',
    ];

    public function due()
    {
        return $this->belongsTo(Due::class);
    }
}
