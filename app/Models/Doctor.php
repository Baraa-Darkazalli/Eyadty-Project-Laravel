<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    // protected $hidden = ['id'];

    public function waitings()
    {
        return $this->hasMany(Waiting::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id', 'id');
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class, 'clinic_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'doctor_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'doctor_id', 'user_id');
    }

    public function users_rating()
    {
        return $this->belongsToMany(User::class,'doctor_ratings', 'doctor_id', 'user_id');
    }

    public function vacation_requests()
    {
        return $this->hasMany(VacationRequest::class, 'doctor_id', 'id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'doctor_id', 'id');
    }

    public function session_duration()
    {
        return $this->belongsTo(SessionDuration::class, 'session_duration_id', 'id');
    }
}
