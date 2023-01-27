<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicDepartment extends Model
{
    use HasFactory;

    public function clinic_names()
    {
        return $this->hasMany(ClinicName::class, 'department_id', 'id');
    }
}
