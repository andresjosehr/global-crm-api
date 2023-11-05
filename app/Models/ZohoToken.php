<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZohoToken extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'zoho_token';
    protected $connection = 'wordpress';
}
