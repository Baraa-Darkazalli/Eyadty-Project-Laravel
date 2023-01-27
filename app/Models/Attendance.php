<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    public function receptions()
    {
        return $this->belongsTo(Reception::class, 'reception_id', 'id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'attendance_employees', 'attendance_id', 'employee_id');
    }
}
