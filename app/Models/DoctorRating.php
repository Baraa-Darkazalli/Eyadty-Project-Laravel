<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorRating extends Model
{
    use HasFactory;

    public function rating_doctor_value()
    {
        return $this->belongsTo(RatingDoctorValue::class, 'rating_doctor_value_id', 'id');
    }
}
