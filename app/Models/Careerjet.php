<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Careerjet extends Model
{
    use HasFactory;

    // disable timestamps
    public $timestamps = false;

    // table
    protected $table = 'careerjet';

    // fillable
    protected $fillable = [
        'locations',
        'site',
        'date',
        'url',
        'title',
        'description',
        'company',
        'country',
        'salary',
        'salary_currency_code',
        'salary_max',
        'salary_min',
        'salary_type',
        'created_at',
        'updated_at'
    ];
}
