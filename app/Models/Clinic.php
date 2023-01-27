<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    public $fillabe = ['clinic_name_id', 'session_price'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'clinic_id', 'id');
    }

    public function clinic_name()
    {
        return $this->belongsTo(ClinicName::class, 'clinic_name_id', 'id');
    }

    public function nurses()
    {
        return $this->hasMany(Nurse::class, 'clinic_id', 'id');
    }

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'clinic_id', 'id');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'clinic_id', 'id');
    }
    public function nurse()
    {
        return $this->hasOne(Nurse::class);
    }
}
