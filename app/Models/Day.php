<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    use HasFactory;

    public function working_hours()
    {
        return $this->belongsToMany(WorkingHour::class, 'working_hour_days', 'day_id', 'working_hour_id');
    }
}
