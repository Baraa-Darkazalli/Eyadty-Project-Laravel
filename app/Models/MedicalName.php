<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalName extends Model
{
    use HasFactory;

    public function medicine()
    {
        return $this->hasOne(Medicine::class, 'medical_name_id', 'id');
    }
}
