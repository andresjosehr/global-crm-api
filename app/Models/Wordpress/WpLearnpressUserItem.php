<?php

namespace App\Models\Wordpress;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpLearnpressUserItem extends Model
{
    use HasFactory;

    protected $connection = 'wordpress';

    protected $table = 'leanpress_user_items';
}
