<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationRequest extends Model
{
    use HasFactory;

    public function doctors()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }
}
