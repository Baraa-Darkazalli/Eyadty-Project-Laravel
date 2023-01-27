<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RatingDoctorValue extends Model
{
    use HasFactory;

    protected $table = 'rating_doctor_values';

    public function doctor_ratings()
    {
        return $this->hasMany(DoctorRating::class, 'rating_doctor_value_id', 'id');
    }
}
