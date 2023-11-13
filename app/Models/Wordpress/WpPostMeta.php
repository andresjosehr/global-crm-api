<?php

namespace App\Models\Wordpress;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpPostMeta extends Model
{
    use HasFactory;

    protected $connection = 'wordpress';
    protected $table = 'postmeta';
    // primary key
    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'post_id',
        'meta_key',
        'meta_value',
    ];

    // Disable timestamps
    public $timestamps = false;


}
