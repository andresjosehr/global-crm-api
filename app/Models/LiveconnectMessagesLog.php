<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveconnectMessagesLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel_id',
        'phone',
        'message',
        'student_id',
        'trigger',
        'message_type',
        'tiggered_by',
        'liveconnect_response'
    ];
}
