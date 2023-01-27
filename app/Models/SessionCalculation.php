<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionCalculation extends Model
{
    use HasFactory;

    public function receptions()
    {
        return $this->belongsTo(Reception::class, 'reception_id', 'id');
    }

    public function session()
    {
        return $this->belongsTo(Session::class, 'session_id', 'id');
    }

    public function extra_treatments()
    {
        return $this->hasMany(ExtraTreatments::class, 'session_calculation_id', 'id');
    }
    public function extraTreatment()
    {
        return $this->hasMany(ExtraTreatments::class, 'session_calculation_id', 'id');
    }
}
