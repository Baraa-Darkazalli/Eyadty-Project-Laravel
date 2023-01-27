<?php

namespace App\Models;

use Database\Factories\TestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    use HasFactory;

    protected $fillable = ['start', 'end'];

    protected static function newFactory()
    {
        return TestFactory::new();
    }

    public function days()
    {
        return $this->belongsToMany(Day::class, 'working_hour_days', 'working_hour_id', 'day_id');
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'working_hour_id', 'employee_id');
    }
}
