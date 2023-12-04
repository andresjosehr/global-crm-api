<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleActivity extends Model
{
    use HasFactory;

    protected $table = 'sales_activities';

    protected $fillable = [
        'start',
        'end',
        'user_id',
        'lead_id',
        'type',
        'lead_assignment_id',
        'answered',
        'observation',
        'schedule_call_datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leadAssignment()
    {
        return $this->belongsTo(LeadAssignment::class);
    }

    public function getDurationAttribute()
    {
        $fechaInicio = Carbon::parse($this->start);
        $fechaFin = Carbon::parse($this->end);

        $diff = $fechaInicio->diff($fechaFin);

        // Comprobamos si los minutos son 0
        if ($diff->i == 0) {
            return $diff->format('%s segundos');
        } else {
            return $diff->format('%i minutos %s segundos');
        }
    }
}
