<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapTry extends Model
{
    use HasFactory;

    protected $fillable = [
        "restrictions",
        "start_datetime",
        "end_datetime",
        "staff_id",
    ];

    public function sapInstalation()
    {
        return $this->belongsTo(SapInstalation::class, 'sap_instalation_id');
    }
}
