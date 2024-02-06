<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Process extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'command',
        'related_entity',
        'related_entity_id',
        'datetime_to_execute',
    ];
}
