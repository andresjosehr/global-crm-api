<?php

namespace App\Models;

use App\Models\Wordpress\LeanpressUserItem;
use App\Models\Wordpress\Post;
use App\Models\Wordpress\WpLearnpressUserItem;
use App\Scopes\OrderByFieldScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new OrderByFieldScope('type'));
    }


    function prices()
    {
        return $this->belongsToMany(Price::class, 'course_prices', 'course_id', 'price_id');
    }

    function course_wp()
    {
        return $this->belongsTo(Post::class, 'wp_post_id', 'ID');
    }

    public function wpLearnpressUserItem()
    {
        return $this->hasOne(WpLearnpressUserItem::class, 'ref_id', 'wp_post_id');
    }


}

