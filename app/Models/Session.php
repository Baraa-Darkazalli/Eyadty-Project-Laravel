<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }
    public function waiting()
    {
        return $this->belongsTo(Waiting::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function prescription()
    {
        return $this->hasOne(Prescription::class, 'session_id', 'id');
    }

    public function session_calculation()
    {
        return $this->hasOne(SessionCalculation::class, 'session_id', 'id');
    }
    public function sessionCalculation()
    {
        return $this->hasOne(SessionCalculation::class, 'session_id', 'id');
    }
}
