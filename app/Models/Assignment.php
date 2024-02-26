<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        "icon",
        "user_id",
        "title",
        "description",
        "link",
        "resolved_at"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
