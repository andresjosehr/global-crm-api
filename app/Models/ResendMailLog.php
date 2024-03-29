<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResendMailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'from',
        'to',
        'subject',
        'student_id',
        'html',
        'status',
        'response'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
