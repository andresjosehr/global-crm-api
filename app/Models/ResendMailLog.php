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
        'html',
        'status',
        'response'
    ];
}
