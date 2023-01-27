<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingHourEmployees extends Model
{
    use HasFactory;

    protected $table = 'working_hour_employees';

    protected $fillable = [
        'working_hour_id',
        'day_id',
        'employee_id',
    ];
}
