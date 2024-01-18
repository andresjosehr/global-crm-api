<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;


    protected $fillable = [
        'name',
        'courses',
        'phone',
        'lead_project_id',
        'channel_id',
        'chat_date',
        'country_id',
        'city_id',
        'document_type_id',
        'status'
    ];

    public function leadAssignments()
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function observations()
    {
        return $this->hasMany(LeadObservation::class)->orderBy('created_at', 'desc');
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function leadProject(){
        return $this->belongsTo(LeadProject::class, 'lead_project_id', 'id');
    }

    public function saleActivities()
    {
        return $this->hasMany(SaleActivity::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
