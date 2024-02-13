<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZadarmaStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'billcost',
        'billseconds',
        'hangupcause',
        'disposition',
        'cost',
        'description',
        'callstart',
        'sip',
        'z_id',
        'from',
        'to',
        'extension',
    ];
}
