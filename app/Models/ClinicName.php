<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicName extends Model
{
    protected $table = 'clinic_names';

    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function clinics()
    {
        return $this->hasMany(Clinic::class, 'clinic_name_id', 'id');
    }

    public function clinic_department()
    {
        return $this->belongsTo(ClinicDepartment::class, 'department_id', 'id');
    }
}
