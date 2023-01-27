<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disease extends Model
{
    use HasFactory;

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'disease_patients', 'disease_id', 'patient_id');
    }
}
