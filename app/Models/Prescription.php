<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    public function medical_analyses()
    {
        return $this->hasMany(MedicalAnalysis::class, 'prescription_id', 'id');
    }

    public function medicines()
    {
        return $this->hasMany(medicine::class, 'prescription_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }
}
