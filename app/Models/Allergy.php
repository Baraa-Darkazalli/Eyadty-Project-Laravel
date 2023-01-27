<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergy extends Model
{
    use HasFactory;

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'allergy_patients', 'allergy_id', 'patient_id');
    }
}
