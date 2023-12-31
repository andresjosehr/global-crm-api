<?php

namespace App\Models\Wordpress;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpPost extends Model
{
    use HasFactory;

    protected $connection = 'wordpress';

    protected $table = 'posts';


    public function meta() {
        return $this->hasMany(WpPostMeta::class, 'post_id', 'ID');
    }
}
