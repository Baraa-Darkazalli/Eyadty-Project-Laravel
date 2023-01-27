<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtraTreatments extends Model
{
    use HasFactory;

    protected $table = 'extra_treatments';

    public function session_calaulation()
    {
        return $this->belongsTo(SessionCalculation::class, 'session_calculation_id', 'id');
    }
}
