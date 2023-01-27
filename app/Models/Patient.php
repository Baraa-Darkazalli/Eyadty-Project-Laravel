<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $hidde = ['id'];

    public function waitings()
    {
        return $this->hasMany(Waiting::class);
    }

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id', 'id');
    }

    public function allergies()
    {
        return $this->belongsToMany(Allergy::class, 'allergy_patients', 'patient_id', 'allergy_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id', 'id');
    }

    public function blood_types()
    {
        return $this->belongsTo(BloodType::class, 'blood_type_id', 'id');
    }

    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'disease_patients', 'patient_id', 'disease_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'patient_id', 'id');
    }
}
