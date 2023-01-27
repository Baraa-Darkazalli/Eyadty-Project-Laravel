<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waiting extends Model
{
    use HasFactory;

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
    public function session()
    {
        return $this->hasOne(Session::class);
    }
}
