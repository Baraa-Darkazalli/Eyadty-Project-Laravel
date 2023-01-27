<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAnalysisName extends Model
{
    use HasFactory;

    public function medical_analyse()
    {
        return $this->hasOne(MedicalAnalysis::class, 'medical_analysis_name_id', 'id');
    }
}
