<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    public function medical_name()
    {
        return $this->belongsTo(MedicalName::class, 'medical_name_id', 'id');
    }

    public function prescriptions()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'id');
    }
}
