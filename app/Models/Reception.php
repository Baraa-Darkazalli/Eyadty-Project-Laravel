<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reception extends Model
{
    use HasFactory;

    protected $hidden = ['id'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'reception_id', 'id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'reception_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function session_calculations()
    {
        return $this->hasMany(SessionCalculation::class, 'reception_id', 'id');
    }
}
