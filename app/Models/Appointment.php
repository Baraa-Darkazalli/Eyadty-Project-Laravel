<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable=[
        'appointment_statue_id'
    ];
    public static function checkIfAvailable($date,$time,$doctor_id)
    {
        $check=Appointment::whereTime('appointment_time','=',$time)
        ->whereDate('appointment_date','=',$date)
        ->where('doctor_id',$doctor_id)
        ->whereIn('appointment_statue_id',[2,4])
        ->exists();
        $check2=Appointment::whereTime('appointment_time','=',$time)
        ->whereDate('appointment_date','=',$date)
        ->where('doctor_id',$doctor_id)
        ->doesntExist();
        if($check || $check2)
            return true;
    }
    public function clinics()
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

    public function receptions()
    {
        return $this->belongsTo(Reception::class, 'reception_id', 'id');
    }

    public function session()
    {
        return $this->hasOne(Session::class, 'appointment_id', 'id');
    }

    public function appointment_statue()
    {
        return $this->belongsTo(AppointmentStatue::class, 'appointment_statue_id', 'id');
    }
    public function waiting()
    {
        return $this->hasOne(Waiting::class);
    }
}
