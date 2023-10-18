<?php

namespace App\Models\Wordpress;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WpLearnpressUserItem extends Model
{
    use HasFactory;

    protected $connection = 'wordpress';

    protected $table = 'learnpress_user_items';


    public function course(){
        return $this->belongsTo(WpPost::class, 'ref_id', 'ID');
    }

    public function item(){
        return $this->belongsTo(WpPost::class, 'item_id', 'ID');
    }
}
