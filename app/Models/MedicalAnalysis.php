<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAnalysis extends Model
{
    use HasFactory;

    protected $table = 'medical_analyses';

    public function medical_analysis_name()
    {
        return $this->belongsTo(MedicalAnalysisName::class, 'medical_analysis_name_id', 'id');
    }

    public function prescriptions()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id', 'id');
    }
}
