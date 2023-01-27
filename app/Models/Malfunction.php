<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Malfunction extends Model
{
    use HasFactory;

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    public function malfunction_statue()
    {
        return $this->belongsTo(MalfunctionStatue::class, 'malfunction_statue_id', 'id');
    }
}
