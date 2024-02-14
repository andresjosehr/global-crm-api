<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
        'project_id'
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

    public function saleActivities()
    {
        return $this->hasMany(SaleActivity::class);
    }

    // En LeadAssignment.php

    public function zadarmaStatistics()
    {
        return ZadarmaStatistic::whereHas('user', function ($query) {
            $query->where('id', $this->user_id);
        })
            ->whereHas('lead', function ($query) {
                $query->where('id', $this->lead_id);
            });
    }
}
