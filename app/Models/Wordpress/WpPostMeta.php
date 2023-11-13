<?php

namespace App\Models\Wordpress;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpPostMeta extends Model
{
    use HasFactory;

    protected $connection = 'wordpress';
    protected $table = 'postmeta';

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];

    // Disable timestamps
    public $timestamps = false;
}
