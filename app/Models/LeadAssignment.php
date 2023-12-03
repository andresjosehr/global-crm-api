<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'user_id',
        'active',
        'assigned_at',
        'order',
        'round',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function observations()
    {
        return $this->hasMany(LeadObservation::class);
    }

    public function comunications()
    {
        return $this->hasMany(SaleActivity::class);
    }

    public function calls()
    {
        return $this->hasMany(SaleActivity::class)->where('type', 'Llamada');
    }
}
